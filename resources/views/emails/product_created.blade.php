<!DOCTYPE html>
<html>
<head>
    <title>New Product Created</title>
</head>
<body>
<h1>New Product Created</h1>
<p>Product Name: {{ $product->name }}</p>
<p>Description: {{ $product->description }}</p>
<p>Price: ${{ $product->price }}</p>
<p>Quantity: {{ $product->quantity }}</p>
</body>
</html>
