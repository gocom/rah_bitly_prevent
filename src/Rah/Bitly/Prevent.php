<?php

/*
 * rah_bitly_prevent - Prevent module for Bitly Textpattern CMS integration
 * https://github.com/gocom/rah_bitly_prevent
 *
 * Copyright (C) 2022 Jukka Svahn
 *
 * This file is part of rah_bitly_prevent.
 *
 * rah_bitly_prevent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_bitly_prevent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_bitly_prevent. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Plugin class.
 */
final class Rah_Bitly_Prevent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        register_callback([$this, 'install'], 'plugin_lifecycle.rah_bitly_prevent', 'installed');
        register_callback([$this, 'uninstall'], 'plugin_lifecycle.rah_bitly_prevent', 'deleted');
        register_callback([$this, 'filter'], 'rah_bitly.permlink');
        register_callback([$this, 'renderSectionOptions'], 'section_ui', 'extend_detail_form');
        register_callback([$this, 'saveSection'], 'section', 'section_save');
    }

    /**
     * Installer.
     */
    public function install(): void
    {
        if (!in_array('rah_bitly_prevent_publish', getThings('describe '.safe_pfx('txp_section')))) {
            safe_alter('txp_section', 'ADD rah_bitly_prevent_publish TINYINT(1) NOT NULL DEFAULT 1');
        }
    }

    /**
     * Uninstaller.
     */
    public function uninstall(): void
    {
        if (in_array('rah_bitly_prevent_publish', getThings('describe '.safe_pfx('txp_section')))) {
            safe_alter('txp_section', 'DROP COLUMN rah_bitly_prevent_publish');
        }
    }

    /**
     * Filters short link generation based on article data.
     *
     * @param string $event
     * @param string $step
     * @param array $data
     */
    public function filter($event, $step, &$data): void
    {
        if (!$this->canPublishArticle($data['articleData'])) {
            $data['permlink'] = null;
        }
    }

    /**
     * Shows settings at the Sections panel.
     *
     * @param string $event The event
     * @param string $step The step
     * @param bool $void Not used
     * @param array $r The section data as an array
     *
     * @return string HTML
     */
    public function renderSectionOptions($event, $step, $void, $r): string
    {
        if ($r['name'] !== 'default') {
            return inputLabel(
                'rah_bitly_prevent_allow_publish',
                yesnoradio('rah_bitly_prevent_publish', $r['rah_bitly_prevent_publish'] ?? '0', '', ''),
                '',
                'rah_bitly_prevent_publish'
            );
        }

        return '';
    }

    /**
     * Updates a section.
     */
    public function saveSection(): void
    {
        safe_update(
            'txp_section',
            'rah_bitly_prevent_publish = '.intval(ps('rah_bitly_prevent_publish')),
            "name = '".doSlash(ps('name'))."'"
        );
    }

    /**
     * Whether the given article data can be published.
     *
     * @param array $articleData
     *
     * @return bool
     */
    private function canPublishArticle(array $articleData): bool
    {
        return (bool) safe_field(
            'rah_bitly_prevent_publish',
            'txp_section',
            "name = '".doSlash($articleData['Section'])."'"
        );
    }
}
