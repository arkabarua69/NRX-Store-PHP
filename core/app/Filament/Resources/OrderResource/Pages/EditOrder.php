<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Constants\OrderStatus;
use App\Constants\Status;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $data = $this->form->getState();

        try {
            $order = Order::where('id', $data['id'])->first();
            if ($order && ($order->status === OrderStatus::PROCESSING || $order->status === OrderStatus::AUTOPROCESSING) && $data['status'] === Status::CANCELLED) {
                OrderService::cancelOrder($order);
                Notification::make()
                    ->title('Successfully refunded.')
                    ->warning()
                    ->body('The order amount has been refunded to the user\'s account.')
                    ->send();
            }
        } catch (ModelNotFoundException $e) {
            //
        }
    }
}
