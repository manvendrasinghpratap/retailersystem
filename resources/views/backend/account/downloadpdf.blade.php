<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Staff Listing</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    @include('backend.layouts.pdfcss')
</head>

<body>
    <div class="container">
        @include('backend.layouts.pdfheader', $pdfHeaderdata)
        <div class="inv-body">
            <table>
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Subscription</th>
                        <th>Hired Date</th>
                        <th>Active Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php($i = 1)
                    @foreach ($hotelList as $row)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $row->hotel_name }}</td>
                            <td>{{ $row->email }}</td>
                            <td>{{ $row->username }}</td>
                            <td>{{ $row->mobile }}</td>
                            <td>
                            @if(array_key_exists($row->subscription_id, $subscriptionList))
                                    {{ $subscriptionList[$row->subscription_id ] }}
                            @endif
                            </td>
                            <td>{{ $row->hire_date->format('d-m-Y') }}</td>
                            <td>{{ $row->status }}</td>
                        </tr>
                        @php($i++)
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
