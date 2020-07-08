<table>
    <thead class="thead-light">
        <tr>
            <!-- <th>{{ __('Image') }}</th> -->
            <th>{{ __('Store Name') }}</th>
            <th>{{ __('ASIN') }}</th>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Sold 30 Days') }}</th>
            <th>{{ __('Sold 60 Days') }}</th>
            <th>{{ __('Sold 90 Days') }}</th>
            <th>{{ __('Sold 120 Days') }}</th>
            <th>{{ __('Total Sold') }}</th>
            <th>{{ __('Link') }}</th>
        </tr>
    </thead>
    <tbody>
        @if($products->count())
            @foreach($products as $product)
                <tr>
                    <!-- <td><img src="{{ $product->image }}" width="75px" height="75px"></td> -->
                    <td>{{ $product->account }}</td>
                    <td>{{ $product->asin }}</td>
                    <td>{{ $product->title }}</td>
                    <td>{{ date('m/d/Y', strtotime($product->created_at)) }}</td>
                    <td class="text-center">{{ $product->{'30days'} }}</td>
                    <td class="text-center">{{ $product->{'60days'} }}</td>
                    <td class="text-center">{{ $product->{'90days'} }}</td>
                    <td class="text-center">{{ $product->{'120days'} }}</td>
                    <td class="text-center">{{ $product->{'30days'} + $product->{'60days'} + $product->{'90days'} + $product->{'120days'} }}</td>
                    <td><a href="https://amazon.com/dp/{{$product->asin}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" class="text-center"> No records found. </td>
            </tr>
        @endif
    </tbody>
</table>
