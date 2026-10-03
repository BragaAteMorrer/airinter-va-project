<?php

namespace App\Models;

use Illuminate\Support\Collection;
use SimpleXMLElement;

/**
 * Represents the SimBrief XML instance with some helper methods
 */
class SimBriefXML extends SimpleXMLElement
{
    /**
     * Return a padded flight level
     */
    public function getFlightLevel(): string
    {
        // Main-flight cruise level. SimBrief normally exposes this as
        // general.initial_altitude in feet (e.g. 37000). Keep fallbacks for
        // older/variant OFP payloads, but only use the alternate as a last resort.
        $candidates = [
            (string) ($this->general->initial_altitude ?? ''),
            (string) ($this->general->cruise_altitude ?? ''),
            (string) ($this->alternate->cruise_altitude ?? ''),
        ];

        foreach ($candidates as $candidate) {
            $candidate = strtoupper(trim($candidate));
            $candidate = preg_replace('/^FL\\s*/', '', $candidate);

            if ($candidate === '' || !is_numeric($candidate)) {
                continue;
            }

            $altitude = (float) $candidate;
            $fl = $altitude > 600 ? (int) round($altitude / 100) : (int) round($altitude);

            if ($fl >= 10 && $fl <= 600) {
                return str_pad((string) $fl, 3, '0', STR_PAD_LEFT);
            }
        }

        return '0';
    }

    /**
     * Return the URL to the vmsACARS flight plan file
     *
     * @return string|null
     */
    public function getAcarsXmlUrl()
    {
        if (!empty($this->fms_downloads->vms)) {
            $base_url = $this->fms_downloads->directory;

            return $base_url.$this->fms_downloads->vms->link;
        }

        return null;
    }

    /**
     * Retrieve all of the flightplans
     */
    public function getFlightPlans(): Collection
    {
        $fps = [];
        $base_url = $this->fms_downloads->directory;

        // TODO: Put vmsACARS on top
        if (!empty($this->fms_downloads->vms)) {
            $fps[] = [
                'name' => $this->fms_downloads->vms->name->__toString(),
                'url'  => $base_url.$this->fms_downloads->vms->link,
            ];
        }

        foreach ($this->fms_downloads->children() as $child) {
            if ($child->getName() === 'directory') {
                continue;
            }

            $fps[] = [
                'name' => $child->name->__toString(),
                'url'  => $base_url.$child->link,
            ];
        }

        return collect($fps);
    }

    /**
     * Return a generator which sends out the fix values. This can be a long list
     *
     * @return \Generator
     */
    public function getRoute()
    {
        foreach ($this->navlog->children()->fix as $fix) {
            $type = $fix->type->__toString();
            if ($type === 'apt') {
                continue;
            }

            $ident = $fix->ident->__toString();

            if ($ident === 'TOC' || $ident === 'TOD') {
                continue;
            }

            yield $fix;
        }
    }

    /**
     * Get the route as a string
     */
    public function getRouteString(): string
    {
        if (!empty($this->general->route)) {
            return $this->general->route->__toString();
        }

        $route = [];
        foreach ($this->getRoute() as $fix) {
            $route[] = $fix->ident->__toString();
        }

        return implode(' ', $route);
    }

    /**
     * Retrieve all of the image links
     */
    public function getImages(): Collection
    {
        $images = [];
        $base_url = $this->images->directory;
        foreach ($this->images->map as $image) {
            $images[] = [
                'name' => $image->name->__toString(),
                'url'  => $base_url.$image->link,
            ];
        }

        return collect($images);
    }
}
