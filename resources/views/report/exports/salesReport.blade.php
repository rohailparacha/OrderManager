<table>
    <thead class="thead-light">
        <tr>
            <th> </th>
            @foreach($stores as $store)
                <th>{{ $store }}</th>
            @endforeach
            <th>@if($chartType == 'amt') Total Amount @else Total Quantity @endif</th>;
        </tr>
    </thead>
    <tbody>
        @if($chartType == 'amt')
            @php
                foreach ($dates as $date) {
                    echo '<tr><td>';
                    
                    echo $date . '</td>';
                            
                    foreach ($stores as $store) {
                        $total = 0;
                        $net = 0;

                        foreach ($data as $record) {
                            $isStore = $record['storeName'] === $store;
                            $isDate = $record['o_date'] === $date;

                            if($isDate) {
                                $net += (float) $record['total_amount'];
                            }

                            if($isStore && $isDate) {
                                $total += (float) $record['total_amount'];
                            }
                        }
                        echo '<td>'. $total . '</td>';
                    }
                        echo '<td>'. $net . '</td>';
                    echo '</tr>';
                }
            @endphp
        @else
            @php
                foreach ($dates as $date) {
                    echo '<tr><td>';
                    
                    echo $date . '</td>';
                            
                    foreach ($stores as $store) {
                        $total = 0;
                        $net = 0;

                        foreach ($data as $record) {
                            $isStore = $record['storeName'] === $store;
                            $isDate = $record['o_date'] === $date;

                            if($isDate) {
                                $net += $record['total_quantity'];
                            }

                            if($isStore && $isDate) {
                                $total +=  $record['total_quantity'];
                            }
                        }
                        echo '<td>'. $total . '</td>';
                    }
                        echo '<td>'. $net . '</td>';
                    echo '</tr>';
                }
            @endphp
        @endif
    </tbody>
</table>
