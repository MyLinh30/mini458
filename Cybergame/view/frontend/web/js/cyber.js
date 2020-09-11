require(['jquery'], function ($) {
    let price = $('.price-wrapper').attr('data-price-amount');
    $('#qty').change(function () {
        let quantity = $(this).val();
        let value = $('.product-info-price .price').html().replace(/\d*$/, "");
        let currency = value.replace('.', "");
        let total = price * quantity;
        $('.price-wrapper .price').html(currency.replace(/\d*$/, "") + (Math.round(total * 100) / 100).toFixed(2));
    });
});
