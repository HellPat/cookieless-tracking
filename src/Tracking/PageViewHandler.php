<?php

declare(strict_types=1);

namespace App\Tracking;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class PageViewHandler implements MessageHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(PageView $pageView): void
    {
        $this->connection->executeQuery(
            <<<SQL
                REPLACE INTO `user_tracking` VALUES (:id, :recorder, :url, :recorded_at)
            SQL,
            [
                'id' => $pageView->id->toString(),
                'recorder' => $pageView->recorder,
                'url' => $pageView->url,
                'recorded_at' => $pageView->recordedAt->format('Y-m-d H:i:s.u'),
            ]
        );
    }
}
