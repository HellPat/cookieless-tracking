<?php

declare(strict_types=1);

namespace App\Tracking;

use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

use function trim;

final class IdentifyRequest
{
    public function getId(Request $request): ?UserId
    {
        $etags = $request->getETags();

        if ($etags === []) {
            return null;
        }

        Assert::stringNotEmpty($etags[0]);

        $etag = $etags[0];

        // TODO: somehow the etag is a string in string markers and they must be trimmed.
        //       I don't know why, figure that out.
        $id = trim($etag, '"');

        return UserId::fromString($id);
    }

    public function getOrCreateId(Request $request): UserId
    {
        return $this->getId($request) ?: UserId::create();
    }
}
