/* eslint prefer-arrow-callback: 0 */

require([
    'jquery',
	'jquery/jquery-ui',
	'bootstrapmin',
	'poppermin'
], function ($) {
$(document).ready(function () {
    $(".service_orders__tr").on("click", function () {
        $(this).find(".service_orders_openclose").toggleClass("opened");
        $(this).closest(".service_orders__tr").find(".service_order_collapse").slideToggle();
    });

    $(".asidelinks").on("click", function () {
        $(this).toggleClass("opened");
    });

    $(".custom_select").selectmenu();
    $(".datepicker input").datepicker();

    $(".orderinfo_opener").on("click", function () {
        $(".orderinfo").slideDown();
        $('html, body').animate({scrollTop: $('.orderinfo').offset().top - 100}, 1000);
    });

    $(".search_item_form_quanity .inc").on("click", function () {
        var curr_val = $(this).parents(".search_item_form_quanity").find("input").val();
        var curr_cost = $(this).closest(".search_item").find(".search_item_cost_curr").html();
        if (curr_val < 999) {
            ++curr_val;
            $(this).parents(".search_item_form_quanity").find("input").val(curr_val);
        }
        var curr_total = (curr_val * curr_cost).toFixed(2);
        $(this).closest(".search_item_form").find(".search_item_input_stock").val(curr_total);
    });

    $(".search_item_form_quanity .dec").on("click", function () {
        var curr_val = $(this).parents(".search_item_form_quanity").find("input").val();
        var curr_cost = $(this).closest(".search_item").find(".search_item_cost_curr").html();
        if (curr_val > 1) {
            --curr_val;
            $(this).parents(".search_item_form_quanity").find("input").val(curr_val);
        }
        var curr_total = (curr_val * curr_cost).toFixed(2);
        $(this).closest(".search_item_form").find(".search_item_input_stock").val(curr_total);
    });

    $(".receipt_opener").on("click", function () {
        $(this).toggleClass("opened");
        $(this).closest(".receipt").find(".receipt_content").slideToggle()
    });

    $(".with_menu3").on("click", function () {
        $(this).toggleClass("opened");
        $(this).find(".main_menu_3").slideToggle()
    });

    $(".header_left_menu").on("click", function () {
        $(".main_menu").fadeToggle();
    });

    $(".main_menu_1").on("click", function () {
        if ($(this).hasClass("active")) {
            $(this).removeClass("active");
            $(this).closest(".main_menu_row_col").find(".main_menu_2").removeClass("opened");
        }
        else {
            $(".main_menu_1").removeClass("active");
            $(this).addClass("active");
            $(".main_menu_2").removeClass("opened");
            $(this).closest(".main_menu_row_col").find(".main_menu_2").addClass("opened");
        }
    });

    $(".notification_popup_markall").on("click", function () {
        $(this).fadeOut();
        $(".notification_popup_item").addClass("readed");
    });

    $(".header_notification_opener").on("click", function () {
        $(".header_notification_popup_bg").show()
        $(".header_notification_popup").fadeIn();
    });

    $(".header_notification_popup_bg").on("click", function () {
        $(".header_notification_popup, .header_notification_popup_bg").fadeOut();
    });



});

$(window).on("load resize", function () {
    if ($(window).width() < '1280') {

    }
});

});