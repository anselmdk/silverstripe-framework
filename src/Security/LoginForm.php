<?php

namespace SilverStripe\Security;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;

/**
 * Abstract base class for a login form
 *
 * This class is used as a base class for the different log-in forms like
 * {@link MemberLoginForm} or {@link OpenIDLoginForm}.
 *
 * @author Markus Lanthaler <markus@silverstripe.com>
 */
abstract class LoginForm extends Form
{

    /**
     * Authenticator class to use with this login form
     *
     * Set this variable to the authenticator class to use with this login
     * form.
     * @var string
     */

    protected $authenticator_class;

    /**
     * Get the authenticator instance
     *
     * @return Authenticator Returns the authenticator instance for this login form.
     */
    public function getAuthenticator()
    {
        if (!class_exists($this->authenticator_class) || !is_subclass_of($this->authenticator_class, 'SilverStripe\\Security\\Authenticator')) {
            user_error("The form uses an invalid authenticator class! '{$this->authenticator_class}'"
                . " is not a subclass of 'Authenticator'", E_USER_ERROR);
            return null;
        }
        return Injector::inst()->get($this->authenticator_class);
    }

    /**
     * Get the authenticator name.
     * @return string The friendly name for use in templates, etc.
     */
    public function getAuthenticatorName()
    {
        $authClass = $this->authenticator_class;
        return $authClass::get_name();
    }
}
