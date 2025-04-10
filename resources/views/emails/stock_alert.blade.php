@component('mail::message')
# Low Stock Alert

The stock for **{{ $ingredient->getName() }}** has fallen below 50% of its initial level.

- **Current Stock**: {{ $ingredient->getStock() }}g
- **Initial Stock**: {{ $ingredient->getInitialStock() }}g

Please consider restocking soon to avoid shortages.

@component('mail::button', ['url' => url('/inventory')])
View Inventory
@endcomponent

Thanks,
@endcomponent
