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
 * @package     theme_efape_ava
 * @copyright   2021 FCAV
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    return {
        init: function() {

            $(document).ready(function($) {

                /*
                --: Localmail :--
                */
                var local_mail = $('.path-local-mail');
                if (local_mail.length > 0) {
                    $('h1').text("Email");
                    $('#maincontent').after("<h2>Caixa de entrada</h2>");
                }


                /*
                --: Link para Fale Conosco e para o Guia :--
                */
                $('#nav-drawer [href*=fale-], #nav-drawer [href$=pdf]').attr('target', '_blank');

            });
        }
    };
});