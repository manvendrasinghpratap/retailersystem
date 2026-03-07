<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', async function () {
            const productId = this.dataset.productId;
            const qtyInput = this.closest('.product-cart-btn').querySelector('.cart-qty');
            const quantity = qtyInput ? parseInt(qtyInput.value) : 1;
            const messageBox = document.querySelector('.cart-message');

            try {
                const response = await fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    messageBox.style.display = 'block';
                    messageBox.classList.add('text-success');
                    messageBox.textContent = data.message ?? 'Product added to cart!';
                    // Optional: update cart count
                    if (data.cart_count !== undefined) {
                        document.querySelector('#cart-count').textContent = data.cart_count;
                    }
                } else {
                    messageBox.style.display = 'block';
                    messageBox.classList.add('text-danger');
                    messageBox.textContent = data.message ?? 'Failed to add product.';
                }
            } catch (err) {
                console.error(err);
                alert('Something went wrong. Please try again.');
            }
        });
    });
});
</script>
