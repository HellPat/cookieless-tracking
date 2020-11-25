<?php

declare(strict_types=1);

namespace App\Tracking;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * @psalm-immutable
 */
final class PageView
{
    public UserId $id;
    public string $recorder;
    public string $url;
    public DateTimeImmutable $recordedAt;

    private function __construct(UserId $id, string $recorder, string $url, DateTimeImmutable $recordedAt)
    {
        $this->id         = $id;
        $this->recorder   = $recorder;
        $this->url        = $url;
        $this->recordedAt = $recordedAt;
    }

    public static function fromFrameRequest(UserId $id, Request $request): self
    {
        $url = $request->headers->get('referer');

        return new self(
            $id,
            'frame',
            $url ?? '',
            new DateTimeImmutable('now')
        );
    }

    public static function fromPageTrackingRequest(Request $request): self
    {
        $data = json_decode((string) $request->getContent(), true, JSON_THROW_ON_ERROR);

        Assert::isArray($data);
        Assert::stringNotEmpty($data['id']);
        Assert::stringNotEmpty($data['page']);

        return new self(
            UserId::fromString($data['id']),
            'js_pageview',
            $data['page'],
            new DateTimeImmutable('now')
        );
    }
}
