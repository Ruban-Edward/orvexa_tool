<?php

/**
 * IcsBuilder.php
 *
 * @category Service
 * @purpose  Builds an iCalendar (RFC 5545) file for a meeting so it can be added to Outlook,
 *           Google Calendar or Apple Calendar. Times are stored in the app timezone and written
 *           as UTC.
 */

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

class IcsBuilder
{
    /**
     * @param array  $meeting     orvexa_meeting_details row
     * @param array  $organizer   ['name' => ..., 'email' => ...]
     * @param array  $attendees   list of ['name' => ..., 'email' => ...]
     * @param string $method      PUBLISH for a download, REQUEST for an emailed invite
     */
    public static function meeting(array $meeting, string $location, array $organizer, array $attendees = [], string $method = 'PUBLISH'): string
    {
        $tz    = new DateTimeZone(config('App')->appTimezone ?: 'UTC');
        $utc   = new DateTimeZone('UTC');
        $start = new DateTimeImmutable($meeting['meeting_start_date'] . ' ' . $meeting['meeting_start_time'], $tz);
        $end   = new DateTimeImmutable(($meeting['meeting_end_date'] ?: $meeting['meeting_start_date']) . ' ' . $meeting['meeting_end_time'], $tz);
        if ($end <= $start) {
            $end = $start->modify('+30 minutes');
        }
        $stamp   = static fn (DateTimeImmutable $d) => $d->setTimezone($utc)->format('Ymd\THis\Z');
        $updated = new DateTimeImmutable($meeting['updated_date'] ?? $meeting['created_date'] ?? 'now', $tz);
        $host    = parse_url(base_url(), PHP_URL_HOST) ?: 'orvexa';

        $description = trim((string) ($meeting['meeting_description'] ?? ''));
        if (! empty($meeting['meeting_link'])) {
            $description = trim($description . "\n\nJoin: " . $meeting['meeting_link']);
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Orvexa//Meetings//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:' . ($method === 'REQUEST' ? 'REQUEST' : 'PUBLISH'),
            'BEGIN:VEVENT',
            'UID:meeting-' . (int) $meeting['meeting_details_id'] . '@' . $host,
            'SEQUENCE:' . max(0, $updated->getTimestamp() - strtotime('2024-01-01')) % 2147483647,
            'DTSTAMP:' . $stamp(new DateTimeImmutable('now', $tz)),
            'DTSTART:' . $stamp($start),
            'DTEND:' . $stamp($end),
            'SUMMARY:' . self::text((string) $meeting['meeting_title']),
        ];
        if ($description !== '') {
            $lines[] = 'DESCRIPTION:' . self::text($description);
        }
        if (trim($location) !== '') {
            $lines[] = 'LOCATION:' . self::text($location);
        }
        if (! empty($meeting['meeting_link']) && preg_match('#^https?://#i', $meeting['meeting_link'])) {
            $lines[] = 'URL:' . $meeting['meeting_link'];
        }
        if (! empty($organizer['email'])) {
            $lines[] = 'ORGANIZER;CN=' . self::param($organizer['name'] ?? '') . ':mailto:' . $organizer['email'];
        }
        foreach ($attendees as $attendee) {
            if (! empty($attendee['email'])) {
                $lines[] = 'ATTENDEE;CN=' . self::param($attendee['name'] ?? '') . ';ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=FALSE:mailto:' . $attendee['email'];
            }
        }
        $lines[] = 'STATUS:' . (($meeting['is_deleted'] ?? 'N') === 'Y' ? 'CANCELLED' : 'CONFIRMED');
        $lines[] = 'BEGIN:VALARM';
        $lines[] = 'ACTION:DISPLAY';
        $lines[] = 'DESCRIPTION:' . self::text((string) $meeting['meeting_title']);
        $lines[] = 'TRIGGER:-PT10M';
        $lines[] = 'END:VALARM';
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([self::class, 'fold'], $lines)) . "\r\n";
    }

    /** Safe file name for the download. */
    public static function fileName(array $meeting): string
    {
        $slug = trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $meeting['meeting_title']), '-');

        return ($slug !== '' ? substr($slug, 0, 60) : 'meeting') . '-' . $meeting['meeting_start_date'] . '.ics';
    }

    private static function text(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $value);
    }

    private static function param(string $value): string
    {
        return '"' . str_replace(['"', "\r", "\n"], ['\'', ' ', ' '], $value) . '"';
    }

    /** Folds a content line at 75 octets without splitting a UTF-8 character. */
    private static function fold(string $line): string
    {
        $out = '';
        $limit = 75;
        while (strlen($line) > $limit) {
            $cut = $limit;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $out .= substr($line, 0, $cut) . "\r\n ";
            $line = substr($line, $cut);
            $limit = 74; // continuation lines start with a space
        }

        return $out . $line;
    }
}
