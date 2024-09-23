<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sale _{{$sale['ref']}}</title>
    <link rel="stylesheet" href="{{asset('/css/pdf_style.css')}}" media="all"/>
</head>

<body>
<header class="clearfix">
    <div id="logo">
        <img src="{{asset('/images/'.$setting['logo']??"no-pic.png")}}">
    </div>
    <div id="company">
        <div><strong> Date : </strong>{{$sale['date']}}</div>
        <div><strong> Number : </strong> {{$sale['ref']}}</div>
        <div><strong> Status : </strong> {{$sale['status']->value}}</div>
        <div><strong> Payment Status : </strong> {{$sale['payment_status']->value}}</div>
    </div>
    <div id="Title-heading">
        Sale : {{$sale['ref']}}
    </div>
</header>
<main>
    <div id="details" class="clearfix">
        <div id="client">
            <table class="table-sm">
                <thead>
                <tr>
                    <th class="desc">Customer Info</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div><strong>Full Name :</strong> {{$sale['client_name']}}</div>
                        <div><strong>Phone :</strong> {{$sale['client_phone']}}</div>
                        <div><strong>Email :</strong> {{$sale['client_email']}}</div>
                        <div><strong>Address :</strong> {{$sale['client_adr']}}</div>
                        @if($sale['client_tax'])
                            <div><strong>Tax Number :</strong> {{$sale['client_tax']}}</div>
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="invoice">
            <table class="table-sm">
                <thead>
                <tr>
                    <th class="desc">Company Info</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <div id="comp">{{$setting['company_name']}}</div>
                        <div><strong>Phone :</strong> {{$setting['company_phone']}}</div>
                        <div><strong>Email :</strong> {{$setting['email']}}</div>
                        <div><strong>Address :</strong> {{$setting['company_address']}}</div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div id="details_inv">
        <table class="table-sm">
            <thead>
            <tr>
                <th>PRODUCT</th>
                <th>UNIT PRICE</th>
                <th>QUANTITY</th>
                <th>DISCOUNT</th>
                <th>TAX</th>
                <th>TOTAL</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($details as $detail)
                <tr>
                    <td>
                        <span>{{$detail['code']}} ({{$detail['name']}})</span>
                        @if($detail['is_imei'] && $detail['imei_number'] !==null)
                            <p>IMEI/SN : {{$detail['imei_number']}}</p>
                        @endif
                    </td>
                    <td>{{$detail['price']}} </td>
                    <td>{{$detail['quantity']}}/{{$detail['unit_sale']}}</td>
                    <td>{{$detail['discount_net']}} </td>
                    <td>{{$detail['tax']}} </td>
                    <td>{{$detail['total']}} </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div id="total">
        <table>
            <tr>
                <td>Order Tax</td>
                <td>{{$sale['tax_net']}} </td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{$sale['discount']}} </td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td>{{$sale['shipping']}} </td>
            </tr>
            <tr>
                <td>Total</td>
                <td>{{$symbol}} {{$sale['grand_total']}} </td>
            </tr>
            <tr>
                <td>Paid Amount</td>
                <td>{{$symbol}} {{$sale['paid_amount']}} </td>
            </tr>
            <tr>
                <td>Due</td>
                <td>{{$symbol}} {{$sale['due']}} </td>
            </tr>
        </table>
    </div>
    <div id="signature">
        @if($setting['is_invoice_footer'] && $setting['invoice_footer'] !==null)
            <p>{{$setting['invoice_footer']}}</p>
        @endif
    </div>
</main>
</body>
</html>
