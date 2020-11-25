<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Tracking\IdentifyRequest;
use App\Tracking\PageView;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

use function str_contains;
use function str_replace;
use function str_starts_with;

final class AttachIdentifier implements EventSubscriberInterface
{
    private const IDENTIFY_URL       = '/identify';
    private const TRACK_PAGEVIEW_URL = '/pageview';

    private MessageBusInterface $messages;
    private Environment $twig;
    private IdentifyRequest $identifier;

    public function __construct(MessageBusInterface $messages, Environment $twig, IdentifyRequest $identifier)
    {
        $this->messages   = $messages;
        $this->twig       = $twig;
        $this->identifier = $identifier;
    }

    /**
     * @return array<string, array|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class  => [
                ['trackPageView', 33], // priority higher that RouterListener
                ['trackPageViewFromJsRequest', 33], // priority higher that RouterListener
            ],
            ResponseEvent::class => 'attachFrame',
        ];
    }

    public function trackPageView(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if (! str_starts_with($request->getPathInfo(), self::IDENTIFY_URL)) {
            return;
        }

        $id         = $this->identifier->getOrCreateId($request);
        $idAsString = $id->toString();

        $response = new Response($idAsString);
        $response->setEtag($idAsString);

        $requestEvent->setResponse($response);
        $requestEvent->stopPropagation();

        // @phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
        if ($response->isNotModified($request)) {
            // TODO: if different handling for existing users is needed
        }

        $this->messages->dispatch(PageView::fromFrameRequest($id, $request));
    }

    public function trackPageViewFromJsRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if (! str_starts_with($request->getPathInfo(), self::TRACK_PAGEVIEW_URL)) {
            return;
        }

        $this->messages->dispatch(PageView::fromPageTrackingRequest($request));

        $requestEvent->setResponse(new Response('', Response::HTTP_NO_CONTENT));
    }

    public function attachFrame(ResponseEvent $responseEvent): void
    {
        if (! $responseEvent->isMasterRequest()) {
            return;
        }

        $response = $responseEvent->getResponse();
        $content  = $response->getContent();

        Assert::string($content);

        if (! str_contains($content, '</body>')) {
            // Do not attach tracking on partial responses and inappropriate response types
            return;
        }

        $contentWithAttachedIdentification = str_replace(
            '</body>',
            '</body>' . $this->createIdentificationSnippet(),
            $content
        );

        $response->setContent($contentWithAttachedIdentification);
    }

    private function createIdentificationSnippet(): string
    {
        return $this->twig->render('_tracking.html.twig', [
            'identify_url' => self::IDENTIFY_URL,
            'pageview_url' => self::TRACK_PAGEVIEW_URL,
        ]);
    }
}
