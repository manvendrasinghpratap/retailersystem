<!DOCTYPE html>
<html>
<head>
    <title>Laravel Barcode Generator</title>
</head>
<body>
    <h1>Product Barcode</h1>
    {{-- Generate the barcode as an SVG image --}}
    {!! DNS1D::getBarcodeSVG('P-0880001', 'C128') !!}
    <p>P-00001</p>
</body>
</html>
