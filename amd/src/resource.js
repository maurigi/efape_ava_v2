// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 *
 * @copyright   2021 FCAV
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/config'], function($, config) {

    return {
        init: function() {
            /**
             * Adiciona barra de navegação de atividades, botão para o topo e rodapé nas atividades arquivo
             */
            function addFooter() {
                var $resourceBody = $("#resourceobject").contents().find("body");
                var $cssLink = $("#resourceobject").contents().find("head > link:first-of-type");
                var font = '<link href="/theme/efape_ava/style/fonts.css" rel="stylesheet">';
                var css = '<link href="/theme/efape_ava/style/footer.css" rel="stylesheet">';

                if ($resourceBody.find('.activity-navigation').length == 0) {
                    $(font).insertBefore($cssLink);
                    $(css).insertBefore($cssLink);
                    $('.activity-navigation a, #page-footer a').attr('target', '_parent');
                    $('.activity-navigation').clone().appendTo($resourceBody);
                    $('.contact').clone().appendTo($resourceBody);
                    $('#page-footer').clone().appendTo($resourceBody);
                    $resourceBody.find('#jump-to-activity').on('change', function() {
                        var url = $(this).val();
                        if (url) {
                            window.parent.location = config.wwwroot + url;
                        }
                        return false;
                    });
                }
            }

            if ($('body').hasClass('path-mod-resource')) {
                addFooter();

                $("#resourceobject").on("load", function() {
                    addFooter();
                });
            }
        }
    };
});