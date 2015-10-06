<?php
namespace abrain\Einsatzverwaltung\Util;

use abrain\Einsatzverwaltung\Data;
use abrain\Einsatzverwaltung\Frontend;
use abrain\Einsatzverwaltung\Options;
use abrain\Einsatzverwaltung\Utilities;
use WP_Post;

/**
 * Formatierungen aller Art
 *
 * @author Andreas Brain
 */
class Formatter
{
    private static $tagsNotNeedingPost = array('%feedUrl%');

    /**
     * @param WP_Post $post
     * @param string $pattern
     * @param array $allowedTags
     *
     * @return mixed
     */
    public static function formatIncidentData($post, $pattern, $allowedTags = array())
    {
        if (empty($allowedTags)) {
            $allowedTags = array_keys(self::getTags());
        }

        $formattedString = $pattern;
        foreach ($allowedTags as $tag) {
            $formattedString = self::format($post, $formattedString, $tag);
        }
        return $formattedString;
    }

    /**
     * @param WP_Post $post
     * @param string $pattern
     * @param string $tag
     * @return mixed|string
     */
    private static function format($post, $pattern, $tag)
    {
        if ($post == null && !in_array($tag, self::$tagsNotNeedingPost)) {
            $message = 'Alle Tags außer ' . implode(',', self::$tagsNotNeedingPost) . ' brauchen ein Post-Objekt';
            _doing_it_wrong(__FUNCTION__, $message, null);
            return '';
        }

        switch ($tag) {
            case '%title%':
                return str_replace('%title%', get_the_title($post), $pattern);
            case '%date%':
                $timeOfAlerting = Data::getAlarmzeit($post->ID);
                $timeOfAlertingTS = strtotime($timeOfAlerting);
                return str_replace('%date%', date_i18n(Options::getDateFormat(), $timeOfAlertingTS), $pattern);
            case '%time%':
                $timeOfAlerting = Data::getAlarmzeit($post->ID);
                $timeOfAlertingTS = strtotime($timeOfAlerting);
                return str_replace('%time%', date_i18n(Options::getTimeFormat(), $timeOfAlertingTS), $pattern);
            case '%duration%':
                return str_replace('%duration%', Utilities::getDurationString(Data::getDauer($post->ID)), $pattern);
            case '%incidentType%':
                return str_replace(
                    '%incidentType%',
                    Frontend::getEinsatzartString(Data::getEinsatzart($post->ID), false, false, false),
                    $pattern
                );
            case '%url%':
                return str_replace('%url%', get_permalink($post->ID), $pattern);
            case '%location%':
                return str_replace('%location%', Data::getEinsatzort($post->ID), $pattern);
            case '%feedUrl%':
                return str_replace('%feedUrl%', get_post_type_archive_feed_link('einsatz'), $pattern);
            default:
                return $pattern;
        }
    }

    /**
     * @return array Ersetzbare Tags und ihre Beschreibungen
     */
    public static function getTags()
    {
        return array(
            '%title%' => __('Titel des Einsatzberichts', 'einsatzverwaltung'),
            '%date%' => __('Datum der Alarmierung', 'einsatzverwaltung'),
            '%time%' => __('Zeitpunkt der Alarmierung', 'einsatzverwaltung'),
            '%duration%' => __('Dauer des Einsatzes', 'einsatzverwaltung'),
            '%incidentType%' => __('Art des Einsatzes', 'einsatzverwaltung'),
            '%url%' => __('URL zum Einsatzbericht', 'einsatzverwaltung'),
            '%location%' => __('Ort des Einsatzes', 'einsatzverwaltung'),
            '%feedUrl%' => __('URL zum Feed', 'einsatzverwaltung'),
        );
    }
}
