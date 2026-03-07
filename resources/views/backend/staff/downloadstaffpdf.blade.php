<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Invoice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <style>
        @media print {
            @page {
                size: A4;
            }
        }

        ul {
            padding: 0;
            margin: 0 0 1rem 0;
            list-style: none;
        }

        body {
            font-family: "Inter", sans-serif;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        table th,
        table td {
            border: 1px solid silver;
        }

        table th,
        table td {
            text-align: right;
            padding: 8px;
        }

        h1,
        h4,
        p {
            margin: 0;
        }

        h1 {
            font-size: 20px;
        }

        .inv-body h4 {
            font-size: 12px;
        }

        th {
            font-size: 12px;
        }

        td {
            font-size: 12px;
        }

        p {
            font-size: 10px;
        }

        .container {
            padding: 2px 0;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        .inv-title {
            padding: 2px;
            border: 1px solid silver;
            text-align: center;
            margin-bottom: 2px;
        }

        .inv-logo {
            width: 150px;
            display: block;
            margin: 0 auto;
            margin-bottom: 40px;
        }

        /* header */
        .inv-header {
            display: flex;
            margin-bottom: 20px;
        }

        .inv-header> :nth-child(1) {
            flex: 2;
        }

        .inv-header> :nth-child(2) {
            flex: 1;
        }

        .inv-header h2 {
            font-size: 14px;
            margin: 0 0 0 0;
        }

        .inv-header ul li {
            font-size: 12px;
            padding: 3px 0;
        }

        /* body */
        .inv-body table th,
        .inv-body table td {
            text-align: left;
        }

        .inv-body {
            margin-bottom: 20px;
        }

        /* footer */
        .inv-footer {
            display: flex;
            flex-direction: row;
        }

        .inv-footer> :nth-child(1) {
            flex: 2;
        }

        .inv-footer> :nth-child(2) {
            flex: 1;
        }

        .floatright {
            float: right;
        }

        .web {
            font-size: 13px !important;
        }

        #parent_div_1,
        #parent_div_2 {
            width: 50%;
            margin-right: 10px;
            float: left;
        }

        .child_div_1 {
            float: left;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="inv-title">
            <h2>Havana Inn Suites(HIS)</h2>
            <h4>Staff Listing</h4><br/>
            <p class="web"><strong>Website:</strong> havanainnsuites.com || <strong>Phone:</strong> 09233322945 |
                09138644346</p>
        </div>
        <div class="inv-body">
            <table>
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Designation</th>
                        <th>Hired Date</th>
                        <th>Active Status </th>
                    </tr>
                </thead>
                <tbody>
                    @php($i = 1)
                    @foreach ($userList as $user)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->designation->name }}</td>
                            <td>{{ $user->hire_date->format('d-m-Y') }}</td>
                            <td>{{ ($user->is_active == 1)?'Active':'In-active' }}</td>
                        </tr>
                        @php($i++)
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
