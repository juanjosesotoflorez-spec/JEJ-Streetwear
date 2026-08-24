let cart = [];


function updateCart() {
    const cartContainer = document.querySelector('.cart-container');
    const cartList = cartContainer.querySelector('ul');
    cartList.innerHTML = '';

    cart.forEach(item => {
        const cartItem = document.createElement('li');
        cartItem.innerHTML = `${item.name} - $${item.price.toLocaleString()}`;
        cartList.appendChild(cartItem);
    });

    const totalPrice = cart.reduce((acc, item) => acc + item.price, 0);
    cartContainer.querySelector('.total').innerHTML = `Total: $${totalPrice.toLocaleString()}`;
}


function openCart() {
    document.querySelector('.cart-container').style.display = 'block';
}


function closeCart() {
    document.querySelector('.cart-container').style.display = 'none';
}


function addToCart(name, price) {
    cart.push({ name, price });
    updateCart();
    alert(`${name} ha sido agregado al carrito.`);
}

// Event listener for all add-to-cart buttons
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', (event) => {
        const productName = event.target.getAttribute('data-name');
        const productPrice = parseInt(event.target.getAttribute('data-price'));
        addToCart(productName, productPrice);
    });
});

// Event listener to open the cart
document.querySelector('.cart-icon').addEventListener('click', openCart);

// Event listener to close the cart
document.querySelector('.close-cart').addEventListener('click', closeCart);

// For the demo, simulate that we have a cart icon in the header
const cartIcon = document.createElement('button');
cartIcon.classList.add('cart-icon');
cartIcon.textContent = 'Ver Carrito';
document.querySelector('header .header-container').appendChild(cartIcon);

// Initialize the cart UI (empty at first)
updateCart();

// Guardar carrito en localStorage
localStorage.setItem("cart", JSON.stringify(cart));

// Recuperar carrito desde localStorage
let savedCart = JSON.parse(localStorage.getItem("cart"));
if (savedCart) {
    cart = savedCart;
    updateCart();
}
