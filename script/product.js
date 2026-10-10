let images = document.querySelectorAll('.product_images');
let ratestart = document.querySelectorAll('.rate-star');
let mainimage = document.querySelector('.main-image');
let modalPhoto = document.getElementById('modalPhoto');

if (images.length > 1) {
    mainimage.src = images[0].src
}

images.forEach(image => {
    image.onclick = function(){
        mainimage.src = this.src
    };
});
mainimage.onclick = function () {
    modalPhoto.src = mainimage.src
    const modalElement = document.getElementById('photoModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}
mainimage.onclick = function () {
    modalPhoto.src = mainimage.src
    const modalElement = document.getElementById('photoModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

 function openReview() {
    const modalElement = document.getElementById('reviewModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}
ratestart.forEach(item => {
    item.onclick = function(){
        document.querySelector('.rating-form').value = this.value
    };
});

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


let selectedColor = null;
let selectedSize = null;
let quantity = 1;
function getVariant(color, size) {
    return variants.find(variant => {
        return (
            variant.color === color &&
            variant.size === size &&
            Number(variant.stock) > 0
        );

    });

}  function getAvailableSizes(color) {
    return variants
        .filter(variant => {
            return (
                variant.color === color &&
                Number(variant.stock) > 0
            );

        })
        .map(variant => variant.size);
}

function getAvailableColors(size) {
    return variants
        .filter(variant => {
            return (
                variant.size === size &&
                Number(variant.stock) > 0
            );

        })
        .map(variant => variant.color);
}