/*! Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

;(function(Icinga, $) {

    'use strict';

    /**
     * Add close button to each announcement element
     *
     * @param {jQuery} $container   The original event
     */
    function onRendered(e) {
        console.log('layout rendered', e);
        var _this = e.data.self;
        var $announcements = $('#announcements > li');

        var c = onCloseButtonClick;

        $announcements.each(function() {
            var $this = $(this);
            if (!$this.children('button.button-close').length > 0) {
                var $closeButton = $('<button class="button-close"><i class="icon-cancel"></i></button>');
                $closeButton.on('click', onCloseButtonClick);
                $this.append($closeButton)
            }
        });
    }

    /**
     * Remove the announcement element
     *
     * @param {object} e   The original event
     */

    function onCloseButtonClick(e) {
        var $t = $(e.target);
        $t.parents('li').remove();
        $('#header').css({
          height: 'auto'
        });
        $('#main, #sidebar, .controls').css({
            top: $('#header').outerHeight()
        });
    }

    Icinga.Behaviors = Icinga.Behaviors || {};

    /**
     * Behavior for announcement banner to handle disposable announce elements
     *
     * The announcement behavior adds a close button to contained announcement elements, which disposes them on click
     *
     * @param {Icinga} icinga
     *
     * @constructor
     */
    var announcementBanner = function(icinga) {
        Icinga.EventListener.call(this, icinga);
        this.on('rendered', '#layout', onRendered, this);
    };

    announcementBanner.prototype = new Icinga.EventListener();

    Icinga.Behaviors.announcementBanner = announcementBanner;
})(Icinga, jQuery);
