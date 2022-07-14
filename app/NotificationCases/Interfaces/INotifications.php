<?php

namespace BuscaAtivaEscolar\NotificationCases\Interfaces;

interface INotifications
{
  public function saveNotificationData(array $attributes): object;
  public function findNotificationData(string $id): ?object;
  public function findAllNotificationDataByUser(string $treeId): ?object;
  public function deleteNotificationData(string $id): bool;
  public function resolveNotificationData(string $id): bool;
  public function getTrees(string $id): string;
  public function checkComment(string $id);
}