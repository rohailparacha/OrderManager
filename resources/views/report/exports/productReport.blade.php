<table>
    <thead>
        <tr>
            <th>{{ __('Store Name') }}</th>
            <th>{{ __('ASIN') }}</th>
            <th>{{ __('UPC') }}</th>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Sold') }}</th>
            <th>{{ __('Returned') }}</th>
            <th>{{ __('Cancelled') }}</th>
            <th>{{ __('Net') }}</th>
            <th>{{ __('Link') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->account }}</td>
                <td>{{ $product->asin }}</td>
                <td>{{ $product->upc }}</td>
                <td>
                    @php
                    if(strlen($product->title) > 25)
                    {
                    $title = substr($product->title,0,25) . '...';
                    }
                    @endphp

                    {{ $title }}
                </td>

                <td>{{ date('m/d/Y', strtotime($product->created_at)) }}</td>
                <td>{{ $product->sold }}</td>
                <td>{{ $product->returned }}</td>
                <td>{{ $product->cancelled }}</td>
                <td>{{ $product->sold - $product->returned - $product->cancelled }} </td>
                <td><a href="https://amazon.com/dp/{{$product->asin}}" class="btn btn-primary btn-sm" target="_blank"><i
                            class="fa fa-external-link-alt"></i> Product</a></td>
            </tr>
        @endforeach
    </tbody>
</table>