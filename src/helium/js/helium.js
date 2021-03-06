/*!
Theme name: Two Point Six
Theme URI: http://www.redmexico.com.mx
Version: 1.0
Author: bitcero
Author URI: http://www.bitcero.info
*/

// @prepros-prepend 'bootstrap-submenu.js';
// @prepros-prepend 'perfect-scrollbar.jquery.js';

function updatesNotifier(count){
    if(count<=0) return;

    $("#updater-info-top").html($("#updater-info-top").html().replace("%s", count));
    $("#updater-info-top").fadeIn('fast');

    if($("#updater-info").length > 0){
        $("#updater-info").html($("#updater-info").html().replace("%s", count));
        $("#updater-info").fadeIn('fast', function(){
            $('html.dashboard [data-container="dashboard"]').trigger('containerUpdated');
        });
    }

}

(function($, lang) {

    $(document).ready(function(){

        var bc_on = false;

        $(".tooltip").each(function(){
            $(this).removeClass("tooltip");
        });

        $("[rel='tooltip']").tooltip({
            animation: true,
            placement: 'bottom'
        });

        $("table.outer").addClass("table").addClass("table-hover");

        $("body").on('click', "[data-action='help'], .cu-help-button", function(){

            $("#he-context-help > .title").html('<span class="fa fa-question-circle"></span> ' + $(this).attr('title'));

            if(undefined != $(this).data('help')){
                var src = cuHandler.url('/modules/rmcommon/help.php?doc=' + $(this).data('help'));

                if($(this).attr('href') != '#'){
                    src += $(this).attr('href');
                }

            } else {
                var src = $(this).attr('href');
            }

            if ( $("#he-context-help > iframe").length > 0 )
                $("#he-context-help iframe").attr("src", src );
            else
                $("#he-context-help").append('<iframe src="' + src + '"></iframe>');

            $("#he-context-help .help-switch").removeClass('fa-question-circle').addClass('fa-angle-double-right');

            $("body").addClass('xo-help');

            return false;

        });

        $("#he-context-help .help-switch").click( function(){

            if ( $("body").hasClass('xo-help') ) {
                $("body").removeClass('xo-help').addClass('xo-without-help');
                $(this).removeClass('fa-angle-double-right').addClass('fa-question-circle');
            } else {
                $("body").removeClass('xo-without-help').addClass('xo-help');
                $(this).removeClass('fa-question-circle').addClass('fa-angle-double-right');
            }

        });

        $("#he-context-help .help-close").click( function(){
            $("body").removeClass('xo-help').removeClass('xo-without-help');
            $("#he-context-help iframe").remove();
            $("#he-context-help .title").html('');
        });

        $('.dropdown-submenu > a').submenupicker();

        $("#he-topbar .toggle-menu").click(function(){
            $("html").toggleClass('sidebar');

            $.cookie('sidebar', $("html").hasClass('sidebar') ? 'visible' : 'hidden', { expires: 30, path: '/' });

            if($("html").hasClass('dashboard')){
                setTimeout(function(){
                    $("[data-box]").trigger('containerUpdated');
                    $("[data-container='dashboard']").trigger('containerUpdated');
                }, 300);
            }

            return false;

        });

        /**
         * Sidebar menu accordion
         */
        $("#he-sidebar a[data-submenu]").click(function(){
            $("#he-sidebar ul.submenu:visible").not($(this).siblings()).slideUp().siblings().toggleClass('open');
            $(this).siblings().slideToggle();
            $(this).toggleClass('open');
            return false;
        });

        /**
         * Prepare scrollbar
         */
        $("#he-sidebar > .menu-wrapper > .sidebar-menu").perfectScrollbar({
            suppressScrollX: true
        });

        $(".he-topbar-collapse > ul > li > .dropdown-menu").perfectScrollbar({
            supressScrollX: true
        });

        $("#he-toolbar").perfectScrollbar({
            suppressScrollY: true,
            useBothWheelAxes: true
        });

        /**
         * CU Boxes
         */
        $("body").on('click', ".cu-box .box-handler", function(){

            var $parent = $(this).parent().parent();
            $parent.toggleClass('collapsed');
            $parent.find('.box-content').slideToggle(function(){
                // Notify to masonry when needed
                if($("html").hasClass('dashboard')){
                    $('[data-container="dashboard"]').trigger('containerUpdated');
                }
            });

        });

        /**
         * Toolbar
         */
        $("#he-toolbar a").click(function(){
            if($(this).hasClass("disabled")){
                return false;
            }
        });

        /**
         * Modal animations
         */
        $("body").on('show.bs.modal', '.modal', function(){
            $(this).removeClass('slideOutDown zoomOut').addClass('animated zoomIn');
        });

        $("body").on('hide.bs.modal', '.modal', function(){
            $(this).removeClass('slideInDown zoomIn').addClass('zoomOut');
        });

        /**
         * Modules search filter
         */
        $("#filter-module").keyup(function() {

            var filter = $(this).val().trim(), count = 0;

            if(filter.length < 1){
                $("#menu-modules > li").fadeIn();
                $(".sidebar-menu .current-module-head").fadeIn();
                $(".sidebar-menu .current-module-menu").fadeIn();
                $(".sidebar-menu .modules-menu-head > a").html(lang.modules);
                return null;
            }

            $(".sidebar-menu .current-module-head").fadeOut();
            $(".sidebar-menu .current-module-menu").fadeOut();

            $("#menu-modules > li").each(function(){
                // If the list item does not contain the text phrase fade it out
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).fadeOut();

                    // Show the list item if the phrase matches and increase the count by 1
                } else {
                    $(this).show();
                    count++;
                }
            });

            $(".sidebar-menu .modules-menu-head > a").html(lang.searchResults.replace('%u',count));

        });

        // Is dashboard?
        if($("html").hasClass('dashboard')){

            var $dashboard, $timer;

            $.getScript(cuHandler.url('/modules/rmcommon/themes/helium/js/masonry.pkgd.min.js'), function(data, textStatus, jqxhr){

                $dashboard = $('[data-container="dashboard"]').masonry({
                    itemSelector: '[data-dashboard="item"]',
                    columnWidth: '.size-1',
                });

            });

            /**
             * When boxes has loaded from server
             */
            $('html.dashboard [data-container="dashboard"]').bind('containerUpdated', function(){
                $dashboard.masonry('reloadItems');
                $dashboard.masonry('layout');
            });

            /**
             * Update layout when resize
             */
            if($("html").hasClass('sidebar')){
                $(window).resize(function(){

                    if($timer > 0){
                        clearTimeout($timer);
                    }

                    $timer = setTimeout(function(){
                        $('[data-container="dashboard"]').trigger('containerUpdated');
                    }, 300);

                });
            }
        }

        // Debug logger
        if($("#he-logger-output").length > 0){

            $("#he-logger-output .close-logger").click(function(){
                $("html").toggleClass('logger');
                return false;
            });

        }


    });

})(jQuery, cuLanguage);