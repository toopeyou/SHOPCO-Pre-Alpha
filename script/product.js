let images = document.querySelectorAll('.product_images');
let mainimage = document.querySelector('.main-image');

if (images.length > 1) {
    mainimage.src = images[0].src

}

images.forEach(image => {
    image.onclick = function(){
        mainimage.src = this.src
    };
});



 function openReview() {
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReview() {
    document.getElementById('reviewModal').style.display = 'none';
}
const colorButtons = document.querySelectorAll('.color-swatch');
const sizeButtons = document.querySelectorAll('.btn-size');

const selectedVariantId = document.getElementById('selectedVariantId');
const selectedColorInput = document.getElementById('selectedColor');
const selectedQuantityInput = document.getElementById('selectedQuantity');
const selectedSizeInput = document.getElementById('selectedSize');

const selectedVariantInfo = document.getElementById('selectedVariantInfo');

const addToCartButton = document.getElementById('addToCart');

const quantityElement = document.getElementById('quantity');

const decreaseQuantity = document.getElementById('decreaseQuantity');
const increaseQuantity = document.getElementById('increaseQuantity');


