@extends('backend.layouts.app')

@section('content')

<div class="card">


        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="md">{{ translate(' Image') }}</th>
                        <th data-breakpoints="md">{{ translate(' Products name') }}</th>
                     
                        <th data-breakpoints="md">{{ translate(' price') }}</th>
                        <th data-breakpoints="md">{{ translate('Total QTY') }}</th>
                        <th data-breakpoints="md">{{ translate('Total price') }}</th>
                        <th data-breakpoints="md">{{ translate('Total paid') }}</th>
                        <th data-breakpoints="md">{{ translate('Total  unpaid') }}</th>
                   
                    </tr>
                </thead>
                <tbody>


                @php
                $totlaQty=0;
                $totalprice=0;
                $totalpaid=0;
                $totalunpaid=0;
                
                                    @endphp
                
                                    @foreach ($Products as $key => $product)
                @php


                $totlaQty+=$product->quantity;
                $totalprice+= $product->price;
                if($product->order->payment_status == "paid")
                {
                    $totalpaid+=$product->price;
                }
                if($product->order->payment_status == "unpaid"){
                    $totalunpaid+=$product->price ;
                }
         
                @endphp
                             
                    @endforeach         
                       
                                    <tr>
                                        <td>
                                        <img src="{{ uploaded_asset($product->product->thumbnail_img)}}" alt="Image" class="size-50px img-fit">
                                    </td>
                                   
                                        <td>
                                            {{ translate($product->product->name) }}
                                        </td>
                                     
                                        <td>
                                            {{ translate($product->price) }}
                                        </td>
                                        <td>
                                            {{ translate( $totlaQty) }}
                                        </td>
                                  
                                        <td>
                                            {{ translate( $totalprice) }}
                                        </td>
                
                                        <td>
                                            {{ translate($totalpaid) }}
                                        </td>
                                      
                                        <td>
                                            {{ translate( $totalunpaid) }}
                                        </td>
                                      
                           
                                
                              
                                </tbody>

                             
                        </table>











                                <div class="card-body">
                                    <table class="table aiz-table mb-0">
                                        <thead>

                    <tr>
                        <!--<th>#</th>-->
                        <th>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{ translate('Order Code') }}</th>
                        <th data-breakpoints="md">{{ translate(' Products name') }}</th>
                        <th data-breakpoints="md">{{ translate('Customer name') }}</th>
                        <th data-breakpoints="md">{{ translate('Seller') }}</th>
                        <th data-breakpoints="md">{{ translate('Qty') }}</th>
                        <th data-breakpoints="md">{{ translate('Delivery Status') }}</th>
                        <th data-breakpoints="md">{{ translate('Payment Status') }}</th>
                        <th data-breakpoints="md">{{ translate('place') }}</th>
                        @if (addon_is_activated('refund_request'))
                        <th>{{ translate('Refund') }}</th>
                        @endif
                        <th class="text-right" width="15%">{{translate('options')}}</th>
                    </tr>
                </thead>
                <tbody>
             
                    



                    @foreach ($Products as $key => $product)

            
       
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" name="id[]" value="{{$product->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                      
                      
                        <td>
                            @if ($product->product_id != null)
                                {{ $product->order->code }}
                           
                            @endif
                        </td>
                  
                        <td>
                            {{ translate($product->product->name) }}
                        </td>

                        <td>
                            {{ translate($product->order->user->name) }}
                        </td>
                        <td>
                            @if($product->order->shop)
                                {{ $order->shop->name }}
                            @else
                                {{ translate('Inhouse Order') }}
                            @endif
                        </td>
                        <td>
                            {{ translate($product->quantity) }}
                        </td>
                        <td>
                            {{ translate(ucfirst(str_replace('_', ' ', $product->order->delivery_status))) }}
                        </td>
                        <td>
                            @if ( $product->order->payment_status == 'paid')
                            <span class="badge badge-inline badge-success">{{translate('Paid')}}</span>
                            @else
                            <span class="badge badge-inline badge-danger">{{translate('Unpaid')}}</span>
                            @endif
                        </td>
                        <td>
                        {{ json_decode($product->order->shipping_address)->country }}-{{ json_decode($product->order->shipping_address)->city }}
                    <br>
                    {{ json_decode($product->order->shipping_address)->address }}
                    
                    </td>
                    <td class="text-right">

                        @can('view_order_details')
                            @php
                                $order_detail_route = route('orders.show', encrypt($product->order->id));
                                if(Route::currentRouteName() == 'seller_orders.index') {
                                    $order_detail_route = route('seller_orders.show', encrypt($product->order->id));
                                }
                                else if(Route::currentRouteName() == 'pick_up_point.index') {
                                    $order_detail_route = route('pick_up_point.order_show', encrypt($product->order->id));
                                }
                                if(Route::currentRouteName() == 'inhouse_orders.index') {
                                    $order_detail_route = route('inhouse_orders.show', encrypt($product->order->id));
                                }
                            @endphp
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ $order_detail_route }}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                        @endcan
                        <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('invoice.download', $product->order->id) }}" title="{{ translate('Download Invoice') }}">
                            <i class="las la-download"></i>
                        </a>
                        @can('delete_order')
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('orders.destroy', $product->order->id)}}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        @endcan
                    </td>
                    </tr>
               
                    @endforeach
                </tbody>
            </table>

            
        </div>
    
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">
        $(document).on("change", ".check-all", function() {
            if(this.checked) {
                // Iterate each checkbox
                $('.check-one:checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.check-one:checkbox').each(function() {
                    this.checked = false;
                });
            }

        });

//        function change_status() {
//            var data = new FormData($('#order_form')[0]);
//            $.ajax({
//                headers: {
//                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                },
//                url: "{{route('bulk-order-status')}}",
//                type: 'POST',
//                data: data,
//                cache: false,
//                contentType: false,
//                processData: false,
//                success: function (response) {
//                    if(response == 1) {
//                        location.reload();
//                    }
//                }
//            });
//        }

        function bulk_delete() {
            var data = new FormData($('#sort_orders')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('bulk-order-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        location.reload();
                    }
                }
            });
        }
    </script>
@endsection
