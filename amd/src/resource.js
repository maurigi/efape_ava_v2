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
            $(document).ready(function() {
                resourceobject();
            });

            /**
             * Verifica a página possui iframe
             */
            function resourceobject() {
                if (document.getElementById('resourceobject')) {
                    checkIframeLoaded();
                }
            }

            /**
             * Verifica se o iframe da página já foi carregado
             */
            function checkIframeLoaded() {
                // Get a handle to the iframe element
                var iframe = document.getElementById('resourceobject');
                var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                switch (iframeDoc.readyState) {
                    case "complete":
                        window.setTimeout(addElementsCss, 100);
                        window.setTimeout(addActivityNavigation, 250);
                        break;
                    default:
                        window.setTimeout(checkIframeLoaded, 100);
                }
            }

            /**
             * Adiciona css
             */
            function addElementsCss() {
                var $cssLink = $("#resourceobject").contents().find("head > link:first-of-type");
                var font = '<link href="' + config.wwwroot + '/theme/efape_ava_v2/style/fonts.css" rel="stylesheet">';
                var css = '<link href="' + config.wwwroot + '/theme/efape_ava_v2/style/footer.css" rel="stylesheet">';
                $(font).insertBefore($cssLink);
                $(css).insertBefore($cssLink);
            }

            /**
             * Adiciona barra de navegação de atividades e rodapé
             */
            function addActivityNavigation() {
                var $resourceBody = $("#resourceobject").contents().find("body");
                $resourceBody.append('<div id="pre-footer"></div>');
                var $preFooter = $("#resourceobject").contents().find("#pre-footer");
                $('.activity-navigation a, #page-footer a').attr('target', '_parent');
                $('.activity-navigation').clone().appendTo($preFooter);
                $('.contact').clone().appendTo($preFooter);
                $('#page-footer').clone().appendTo($resourceBody);
            }
        }
    };
});