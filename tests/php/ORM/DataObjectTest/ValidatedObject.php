<?php

namespace SilverStripe\ORM\Tests\DataObjectTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;

class ValidatedObject extends DataObject implements TestOnly
{
	private static $table_name = 'DataObjectTest_ValidatedObject';

	private static $db = array(
		'Name' => 'Varchar(50)'
	);

	public function validate()
	{
		if (!empty($this->Name)) {
			return new ValidationResult();
		} else {
			return new ValidationResult(false, "This object needs a name. Otherwise it will have an identity crisis!");
		}
	}
}
