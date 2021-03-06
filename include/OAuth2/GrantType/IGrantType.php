<?php

/**
 * Specific GrantType Interface
 */
interface OAuth2_GrantType_IGrantType 
{
    /**
     * Adds a specific Handling of the parameters
     * 
     * @return array of Specific parameters to be sent.
     * @param  mixed  $parameters the parameters array (passed by reference)
     */
    public function validateParameters(&$parameters);
}
