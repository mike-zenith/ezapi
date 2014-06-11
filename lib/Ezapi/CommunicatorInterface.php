<?php

namespace Ezapi;


use Ezapi\Resource\Location;

interface CommunicatorInterface {

    /**
     * @param Location $resource
     * @return Response
     */
    public function query(Location $resource);
} 