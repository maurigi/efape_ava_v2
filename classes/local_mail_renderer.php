<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Overriden course topics format renderer.
 *
 * @package    theme_moove
 * @copyright  2017 Willian Mano - http://conecti.me
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class theme_efape_ava_v2_local_mail_renderer extends local_mail_renderer
{
    private function custom_image_url($imagename, $component = 'moodle')
    {
        if (method_exists($this->output, 'image_url')) {
            return $this->output->image_url($imagename, $component);
        }
        return $this->output->pix_url($imagename, $component);
    }

    public function toolbar($type, $courseid = 0, $params = null)
    {

        $replyall = isset($params['replyall']) ? $params['replyall'] : false;
        $paging = isset($params['paging']) ? $params['paging'] : null;
        $trash = isset($params['trash']) ? $params['trash'] : false;
        $labelid = isset($params['labelid']) ? $params['labelid'] : false;

        $toolbardown = false;
        if ($type === 'reply') {
            $viewcourse = array_key_exists($courseid, local_mail_get_my_courses());
            $output = $this->reply($viewcourse);
            // All recipients.
            $output .= $this->replyall(($viewcourse and $replyall));
            $output .= $this->forward($viewcourse);
            $toolbardown = true;
        } else if ($type === 'forward') {
            $viewcourse = array_key_exists($courseid, local_mail_get_my_courses());
            $output = $this->reply($viewcourse, true);
            // All recipients.
            $output .= $this->replyall(($viewcourse and $replyall), true);
            $output .= $this->forward($viewcourse);
            $toolbardown = true;
        } else {
            $toggle = $this->toggle_buttons();
            $selectall = $this->selectall();
            $labels = $extended = $goback = $search = $selectedlbl = '';
            if (!$trash and $type !== 'trash') {
                $labels = $this->labels($type);
            }
            $read = $unread = '';
            if ($type !== 'drafts') {
                $unread = $this->unread($type);
                $delete = $this->delete($trash);
            } else {
                $delete = $this->discard();
            }
            if ($trash) {
                $delete .= $this->hide();
                if ($type !== 'view') {
                    $delete .= $this->empty_trash();
                }
            }
            $pagingbar = '';
            if ($type !== 'view') {
                if ($type !== 'drafts') {
                    $read = $this->read();
                }
                $pagingbar = $this->paging(
                    $paging['offset'],
                    $paging['count'],
                    $paging['totalcount']
                );
                $search = $this->search();
            } else {
                $goback = $this->goback($paging);
            }
            if ($type === 'label') {
                $extended = $this->optlabels();
                $selectedlbl = $this->label(local_mail_label::fetch($labelid));
            }
            $more = $this->moreactions();
            $clearer = html_writer::div('', 'clearer', array('aria-hidden' => 'true'));
            $left = '';
            if ($type != 'view') {
                $left = $toggle;
            }
            $left .= html_writer::tag(
                'div',
                $goback . $selectall . $labels . $read . $unread . $delete . $extended . $more . $search . $selectedlbl,
                array('class' => 'mail_buttons d-lg-flex')
            );
            $output = $left . $pagingbar . $clearer;
        }
        return html_writer::div($output, ($toolbardown ? 'mail_toolbar_down' : 'mail_toolbar'), array('role' => 'toolbar'));


        // return $this->output->container($output, ($toolbardown ? 'mail_toolbar_down' : 'mail_toolbar'));
    }

    public function goback()
    {
        $label = $this->output->larrow();
        $output = html_writer::start_tag('noscript');
        $output .= '<input type="submit" name="goback" value="' . $label . '" class="mail_button singlebutton d-lg-block" />';
        $output .= html_writer::end_tag('noscript');
        $arguments = array(
            'class' => 'mail_hidden singlebutton mail_button mail_goback d-lg-block',
            'role' => 'button',
            'aria-label' => 'Ir para a lista de mensagens'
        );
        $output .= html_writer::tag('div', $label, $arguments);
        return $output;
    }

    public function labels($type)
    {
        global $USER;

        $items = array();
        $label = get_string('setlabels', 'local_mail');
        $attributes = array(
            'type' => 'submit',
            'name' => 'assignlbl',
            'value' => $label,
            'class' => 'mail_button singlebutton',
            'aria-label' => 'Exiir marcadores'
        );
        $output = html_writer::start_tag('noscript');
        $output .= html_writer::empty_tag('input', $attributes);
        $output .= html_writer::end_tag('noscript');
        $attributes = array(
            'class' => 'singlebutton mail_button mail_assignlbl mail_button_disabled mail_hidden d-lg-block',
            'role' => 'button',
            'aria-haspopup' => 'true'
        );
        $output .= html_writer::start_tag('div', $attributes);
        $output .= html_writer::tag('span', $label);
        $url = $this->custom_image_url('t/expanded', 'moodle');
        $output .= html_writer::empty_tag('img', array('src' => $url, 'alt' => ''));
        $output .= html_writer::end_tag('div');
        // List labels and options.
        $labels = local_mail_label::fetch_user($USER->id);
        $output .= html_writer::start_tag('div', array('class' => 'mail_hidden mail_labelselect', 'role' => 'listbox'));
        foreach ($labels as $key => $label) {
            $attributes = array(
                'class' => 'mail_adv_checkbox mail_checkbox0 mail_label_value_' . $label->id()
            );
            $content = html_writer::tag('span', '', $attributes);
            $content .= html_writer::tag('span', $label->name(), array('class' => 'mail_label_name'));
            $items[$key] = $content;
        }
        if (!empty($labels)) {
            $items[] = html_writer::tag('span', '', array('class' => 'mail_menu_label_separator'));
        }
        $items[] = html_writer::link('#', get_string('newlabel', 'local_mail'), array('class' => 'mail_menu_label_newlabel'));
        if (!empty($labels)) {
            $items[] = html_writer::link('#', get_string('applychanges', 'local_mail'), array('class' => 'mail_menu_label_apply'));
        }
        $output .= html_writer::alist($items, array('class' => 'mail_menu_labels'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function delete($trash)
    {
        $type = ($trash ? 'restore' : 'delete');
        $label = ($trash ? get_string('restore', 'local_mail') : get_string('delete'));
        $attributes = array(
            'type' => 'submit',
            'name' => 'delete',
            'value' => $label,
            'class' => 'mail_button singlebutton',
            'role' => 'button',
        );
        $output = html_writer::start_tag('noscript');
        $output .= html_writer::empty_tag('input', $attributes);
        $output .= html_writer::end_tag('noscript');
        $attributes = array(
            'id' => 'mail_' . $type,
            'class' => 'singlebutton mail_button mail_button_disabled mail_hidden d-lg-block',
            'role' => 'button',
            'aria-label' => 'Excluir mensagem'
        );
        $output .= html_writer::start_tag('div', $attributes);
        $output .= html_writer::tag('span', $label);
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function moreactions()
    {
        $arguments = array(
            'class' => 'mail_hidden singlebutton mail_button mail_more_actions d-lg-block',
            'role' => 'button',
            'aria-haspopup' => 'true',
            'aria-label' => 'Mais ações'
        );
        $output = html_writer::start_tag('span', $arguments);
        $output .= html_writer::tag('span', get_string('moreactions', 'local_mail'));
        $url = $this->custom_image_url('t/expanded', 'moodle');
        $output .= html_writer::empty_tag('img', array('src' => $url, 'alt' => ''));
        $output .= html_writer::end_tag('span');
        // Menu options.
        $output .= html_writer::start_tag('div', array('class' => 'mail_hidden mail_actselect'));
        $items = array(
            'markasread' => get_string('markasread', 'local_mail'),
            'markasunread' => get_string('markasunread', 'local_mail'),
            'markasstarred' => get_string('markasstarred', 'local_mail'),
            'markasunstarred' => get_string('markasunstarred', 'local_mail'),
            'separator' => '',
            'editlabel' => get_string('editlabel', 'local_mail'),
            'removelabel' => get_string('removelabel', 'local_mail')
        );
        foreach ($items as $key => $item) {
            $items[$key] = html_writer::link('#', $item, array('class' => 'mail_menu_action_' . $key));
        }
        $output .= html_writer::alist($items, array('class' => 'mail_menu_actions'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function view($params)
    {
        global $USER;

        $content = '';

        $type = $params['type'];
        $itemid = !empty($params['itemid']) ? $params['itemid'] : 0;
        $userid = $params['userid'];
        $messages = $params['messages'];
        $count = count($messages);
        $offset = $params['offset'];
        $totalcount = $params['totalcount'];
        $ajax = !empty($params['ajax']);
        $mailpagesize = get_user_preferences('local_mail_mailsperpage', MAIL_PAGESIZE, $USER->id);
        $usesvg = $this->page->theme->use_svg_icons();

        if (!$ajax) {
            $url = new moodle_url($this->page->url);
            $params = array(
                'method' => 'post',
                'action' => $url,
                'id' => 'local_mail_main_form',
                'class' => ($usesvg ? 'mail_svg' : ''),
            );
            $content .= html_writer::start_tag('form', $params);
        }
        $paging = array(
            'offset' => $offset,
            'count' => $count,
            'totalcount' => $totalcount,
            'pagesize' => $mailpagesize,
        );
        if (!$messages) {
            $paging['offset'] = false;
        }

        $content .= $this->toolbar($type, 0, array('paging' => $paging, 'trash' => ($type === 'trash'), 'labelid' => $itemid));
        $content .= html_writer::start_tag('div', array('id' => 'mail_loading_small', 'class' => 'mail_hidden mail_loading_small'));
        $content .= $this->output->pix_icon('i/loading_small', '', 'moodle');
        $content .= html_writer::end_tag('div');
        $content .= $this->notification_bar();
        if ($messages) {
            $content .= $this->messagelist($messages, $userid, $type, $itemid, $offset);
        } else {
            $content .= $this->output->container_start('mail_list');
            $string = get_string('nomessagestoview', 'local_mail');
            $initurl = new moodle_url('/local/mail/view.php');
            $initurl->param('t', $type);
            if ($type === 'label') {
                $initurl->param('l', $itemid);
            }
            $link = html_writer::link($initurl, get_string('showrecentmessages', 'local_mail'));
            $content .= html_writer::tag('div', $string . ' ' . $link, array('class' => 'mail_item', 'role' => 'listitem'));
            $content .= $this->output->container_end();
        }
        $content .= html_writer::start_tag('div', array('class' => 'mail_hidden mail_search_loading'));
        $content .= $this->output->pix_icon('i/loading', get_string('actions'), 'moodle', array('class' => 'loading_icon'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div');
        $content .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'type',
            'value' => $type,
        ));
        $content .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey(),
        ));
        $content .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'offset',
            'value' => $offset,
        ));
        $content .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'itemid',
            'value' => $itemid,
        ));
        $content .= $this->editlabelform();
        $content .= $this->newlabelform();
        $content .= $this->searchform();
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'mail_perpage'));
        $content .= $this->perpage($offset, $mailpagesize);
        $content .= html_writer::end_tag('div');
        if (!$ajax) {
            $content .= html_writer::end_tag('form');
        }

        return $this->output->container($content);
    }

    public function references($references, $reply = false)
    {
        $class = 'mail_references';
        $header = 'h2';
        if ($reply) {
            $class = 'mail_reply';
            $header = 'h2';
        }
        $output = $this->output->container_start($class);
        $output .= html_writer::tag($header, get_string('references', 'local_mail'));
        foreach ($references as $ref) {
            $output .= $this->mail($ref, true);
        }
        $output .= $this->output->container_end();
        return $output;
    }

    public function label_message($message, $type, $labelid, $mailview = false)
    {
        global $USER;

        $output = html_writer::start_tag('span', array('class' => 'mail_group_labels'));
        $labels = $message->labels($USER->id);
        foreach ($labels as $label) {
            if ($type === 'label' and $label->id() === $labelid) {
                continue;
            }
            $text = html_writer::tag(
                'span',
                s($label->name()),
                array('class' => 'mail_label mail_label_' . $label->color() . ' mail_label_' . $label->id())
            );
            if ($mailview) {
                $linkparams = array('title' => get_string('showlabelmessages', 'local_mail', s($label->name())));
                $urlparams = array('t' => 'label', 'l' => $label->id());
                $output .= html_writer::link(new moodle_url('/local/mail/view.php', $urlparams), $text, $linkparams);
            } else {
                $output .= $text;
            }
        }

        $output .= html_writer::end_tag('span');
        return $output;
    }

    public function selectall()
    {
        $arguments = array(
            'class' => 'mail_hidden mail_button mail_checkbox_all',
            'role' => 'button',
            'aria-haspopup' => 'true',
            'aria-label' => 'Opções de seleção'
        );
        $output = html_writer::start_tag('div', $arguments);
        $output .= html_writer::tag('span', '', array('class' => 'mail_selectall mail_adv_checkbox mail_checkbox0'));
        $url = $this->custom_image_url('t/expanded', 'moodle');
        $output .= html_writer::empty_tag('img', array('src' => $url, 'alt' => ''));
        $output .= html_writer::end_tag('div');
        // Menu options.
        $output .= html_writer::start_tag('div', array('class' => 'mail_hidden mail_optselect'));
        $items = array(
            'all' => get_string('all', 'local_mail'),
            'none' => get_string('none', 'local_mail'),
            'read' => get_string('read', 'local_mail'),
            'unread' => get_string('unread', 'local_mail'),
            'starred' => get_string('starred', 'local_mail'),
            'unstarred' => get_string('unstarred', 'local_mail')
        );
        foreach ($items as $key => $item) {
            $items[$key] = html_writer::link('#', $item, array('class' => 'mail_menu_option_' . $key));
        }
        $output .= html_writer::alist($items, array('class' => 'mail_menu_options'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function search()
    {
        $attributes = array(
            'id' => 'mail_search',
            'class' => 'mail_hidden mail_search_button singlebutton mail_button',
            'role' => 'button'
        );
        $output = html_writer::start_tag('div', $attributes);
        $output .= html_writer::tag('span', get_string('search', 'local_mail'));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function paging($offset, $count, $totalcount)
    {
        if ($count == 1) {
            $a = array('index' => $offset + 1, 'total' => $totalcount);
            $str = get_string('pagingsingle', 'local_mail', $a);
        } else if ($totalcount > 0) {
            $a = array('first' => $offset + 1, 'last' => $offset + $count, 'total' => $totalcount);
            $str = get_string('pagingmultiple', 'local_mail', $a);
        } else {
            $str = '';
        }

        $str = html_writer::tag('span', $str);

        $prev = '<input value="' . $this->output->larrow() . '" type="submit" name="prevpage" title="'
            . get_string('previous') . '" aria-label="' . get_string('previous') . '" class="mail_button singlebutton"';
        if (!$offset) {
            $prev .= ' disabled="disabled"';
        }
        $prev .= ' />';

        $next = '<input value="' . $this->output->rarrow() . '" type="submit" name="nextpage" title="'
            . get_string('next') . '" aria-label="' . get_string('next') . '" class="mail_button singlebutton"';
        if ($offset === false or ($offset + $count) == $totalcount) {
            $next .= ' disabled="disabled"';
        }
        $next .= ' />';

        $txt = html_writer::tag('span', 'Exibindo ', array('class' => 'sr-only'));

        return $this->output->container($txt . $str . ' ' . $prev . $next, 'mail_paging');
    }

    public function messagelist($messages, $userid, $type, $itemid, $offset)
    {

        $output = $this->output->container_start('mail_list');
        $output .= html_writer::start_tag('table', array('class' => 'w-100'));
        $output .= html_writer::start_tag('tr', array('class' => 'sr-only'));
        $output .= html_writer::tag('th', 'Selecionado');
        $output .= html_writer::tag('th', get_string('starred', 'local_mail'));
        $output .= html_writer::tag('th', get_string('from', 'local_mail'));
        $output .= html_writer::tag('th', get_string('subject', 'local_mail'));
        $output .= html_writer::tag('th', get_string('filterbydate', 'local_mail'));
        $output .= html_writer::end_tag('tr');

        foreach ($messages as $message) {
            $unreadclass = '';
            $attributes = array(
                'type' => 'checkbox',
                'name' => 'msgs[]',
                'value' => $message->id(),
                'class' => 'mail_checkbox'
            );
            $checkbox = html_writer::start_tag('noscript');
            $checkbox .= html_writer::empty_tag('input', $attributes);
            $checkbox .= html_writer::end_tag('noscript');
            $attributes = array(
                'role' => 'checkbox',
                'aria-label' => 'Marcar mensagem',
                'class' => 'mail_hidden mail_adv_checkbox mail_checkbox0 mail_checkbox_value_' . $message->id()
            );
            $checkbox .= html_writer::tag('span', '', $attributes);
            $checkbox = html_writer::tag('td', $checkbox);
            $flags = '';
            if ($type !== 'trash') {
                $flags = $this->starred($message, $userid, $type, $offset);
            }
            $flags = html_writer::tag('td', $flags);
            $context = context_course::instance($message->course()->id);
            $draftmessage = ($message->editable($userid) and
                (array_key_exists($message->course()->id, local_mail_get_my_courses()) or
                    has_capability('moodle/course:view', $context)));
            if ($draftmessage) {
                $url = new moodle_url('/local/mail/compose.php', array('m' => $message->id()));
            } else {
                $params = array('t' => $type, 'm' => $message->id(), 'offset' => $offset);
                $type == 'course' and $params['c'] = $itemid;
                $type == 'label' and $params['l'] = $itemid;
                $url = new moodle_url("/local/mail/view.php", $params);
            }
            if ($message->unread($userid)) {
                $unreadclass = 'mail_unread';
            }
            $output .= html_writer::start_tag('tr', array('class' => 'mail_item ' . $unreadclass));
            $attributes = array('href' => $url, 'class' => 'mail_link');

            $content = (html_writer::tag('td', $this->users($message, $userid, $type, $itemid)) .
                html_writer::tag('td', html_writer::tag('a', $this->summary($message, $userid, $type, $itemid) . $this->attachment($message), $attributes)) .
                html_writer::tag('td', $this->date($message)));
            $output .= $checkbox . $flags . $content;
            $output .= html_writer::end_tag('tr');
        }

        $output .= html_writer::end_tag('table');

        $output .= $this->output->container_end();

        return $output;
    }

    public function summary($message, $userid, $type, $itemid)
    {
        global $DB;

        $content = '';

        if ($type != 'drafts' and $message->draft()) {
            $content .= $this->label_draft();
        }

        if ($type != 'course' or $itemid != $message->course()->id) {
            $content .= $this->label_course($message->course());
        }

        $content .= $this->label_message($message, $type, $itemid);

        if ($message->subject()) {
            $content .= s($message->subject());
        } else {
            $content .= get_string('nosubject', 'local_mail');
        }
        return html_writer::tag('span', $content, array('class' => 'mail_summary'));
    }

    public function mail($message, $reply = false, $offset = 0)
    {
        global $CFG, $USER;

        $totalusers = 0;
        $output = '';

        if (!$reply) {
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden',
                'name' => 'm',
                'value' => $message->id(),
            ));

            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden',
                'name' => 'offset',
                'value' => $offset,
            ));
        }

        $output .= $this->output->container_start('mail_header');
        $output .= $this->output->container_start('left');
        $user_picture = $this->output->user_picture($message->sender());
        $output .= html_writer::span($user_picture, '', array('aria-hidden' => 'true'));
        $output .= $this->output->container_end();
        $output .= $this->output->container_start('mail_info');
        $output .= html_writer::span('Enviado por ', 'sr-only');
        $output .= html_writer::link(
            new moodle_url(
                '/user/view.php',
                array(
                    'id' => $message->sender()->id,
                    'course' => $message->course()->id
                )
            ),
            fullname($message->sender()),
            array('class' => 'user_from')
        );
        $output .= html_writer::span(' Em ', 'sr-only');
        $output .= $this->date($message, true);
        if (!$reply) {
            $output .= $this->output->container_start('mail_recipients');
            foreach (array('to', 'cc', 'bcc') as $role) {
                $recipients = $message->recipients($role);
                if (!empty($recipients)) {
                    if ($role == 'bcc' and $message->sender()->id !== $USER->id) {
                        continue;
                    }
                    $output .= html_writer::start_tag('div');
                    $output .= html_writer::tag('span', get_string($role, 'local_mail'), array('class' => 'mail_role'));
                    $numusers = count($recipients);
                    $totalusers += $numusers;
                    $cont = 1;
                    foreach ($recipients as $user) {
                        $output .= html_writer::link(
                            new moodle_url(
                                '/user/view.php',
                                array(
                                    'id' => $user->id,
                                    'course' => $message->course()->id
                                )
                            ),
                            fullname($user)
                        );
                        if ($cont < $numusers) {
                            $output .= ', ';
                        }
                        $cont += 1;
                    }
                    $output .= ' ';
                    $output .= html_writer::end_tag('div');
                }
            }
            $output .= $this->output->container_end();
        } else {
            $output .= html_writer::tag('div', '', array('class' => 'mail_recipients'));
        }
        $output .= $this->output->container_end();
        $output .= $this->output->container_end();

        $output .= $this->output->container_start('mail_body');
        $output .= $this->output->container_start('mail_content');
        $output .= local_mail_format_content($message);
        $attachments = local_mail_attachments($message);
        if ($attachments) {
            $output .= $this->output->container_start('mail_attachments');
            if (count($attachments) > 1) {
                $text = get_string('attachnumber', 'local_mail', count($attachments));
                $output .= html_writer::tag('span', $text, array('class' => 'mail_attachment_text'));
                $urlparams = array(
                    't' => 'course',
                    'm' => $message->id(),
                    'downloadall' => '1',
                );
                $downloadurl = new moodle_url('/local/mail/view.php', $urlparams);
                $text = get_string('downloadall', 'local_mail');
                $iconimage = $this->output->pix_icon('a/download_all', $text, 'moodle', array('class' => 'icon'));
                $output .= html_writer::start_div('mail_attachment_downloadall');
                $output .= html_writer::link($downloadurl, $iconimage);
                $text = get_string('downloadall', 'local_mail');
                $output .= html_writer::link($downloadurl, $text, array('class' => 'mail_downloadall_text'));
                $output .= html_writer::end_div();
            }
            foreach ($attachments as $attach) {
                $filename = $attach->get_filename();
                $filepath = $attach->get_filepath();
                $mimetype = $attach->get_mimetype();
                $iconimage = $this->output->pix_icon(
                    file_file_icon($attach),
                    get_mimetype_description($attach),
                    'moodle',
                    array('class' => 'icon')
                );
                $path = '/' . $attach->get_contextid() . '/local_mail/message/' . $attach->get_itemid() . $filepath . $filename;
                $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, true);
                $output .= html_writer::start_tag('div', array('class' => 'mail_attachment'));
                $output .= html_writer::link($fullurl, $iconimage);
                $output .= html_writer::link($fullurl, s($filename));
                $output .= html_writer::tag(
                    'span',
                    display_size($attach->get_filesize()),
                    array('class' => 'mail_attachment_size')
                );
                $output .= html_writer::end_tag('div');
            }
            $output .= $this->output->container_end();
        }
        $output .= $this->output->container_end();
        $output .= $this->newlabelform();
        if (!$reply) {
            if ($message->sender()->id !== $USER->id) {
                $output .= $this->toolbar('reply', $message->course()->id, array('replyall' => ($totalusers > 1)));
            } else {
                $output .= $this->toolbar('forward', $message->course()->id, array('replyall' => ($totalusers > 1)));
            }
        }
        $output .= $this->output->container_end();
        return $output;
    }
}