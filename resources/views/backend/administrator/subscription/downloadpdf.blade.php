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
                        <th>Description</th>
                        <th>Price</th>
                        <th>Duration (Years)</th>
                        <th>Created At</th>
                        <th>Active Status </th>
                    </tr>
                </thead>
                <tbody>
                    @php($i = 1)
                    @foreach ($subscriptionList as $subscription)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $subscription->name }}</td>
                            <td>{{ $subscription->description }}</td>
                            <td>{{ $subscription->price }}</td>
                            <td>{{ $subscription->duration }}</td>
                            <td>{{ $subscription->created_at->format('d-m-Y') }}</td>
                            <td>{{ $subscription->status }}</td>
                        </tr>
                        @php($i++)
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
