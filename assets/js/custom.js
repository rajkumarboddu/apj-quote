$(document).ready(function() {
    $(".price-to-format").each(function() {
        var price = parseFloat($(this).text());
        $(this).html(price.toLocaleString('en-IN'));
    });
});