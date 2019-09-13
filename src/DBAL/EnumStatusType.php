<?php
namespace App\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class EnumStatusType extends Type
{
    const ENUM_STATUS = 'enumstatus';

    const NEW 		= 'new';
    const PENDING 	= 'pending';
    const IN_REVIEW = 'in review';
    const APPROVED 	= 'approved';
    const INACTIVE 	= 'inactive';
    const DELETED 	= 'deleted';

    protected static $values = [
        self::NEW,
        self::PENDING,
        self::IN_REVIEW,
        self::APPROVED,
        self::INACTIVE,
        self::DELETED,
    ];


    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('{self::NEW}', '{self::PENDING}', '{self::IN_REVIEW}', '{self::APPROVED}', '{self::INACTIVE}', '{self::DELETED}')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::$values)) {
            throw new \InvalidArgumentException("Invalid status");
        }
        return $value;
    }

    public function getName()
    {
        return self::ENUM_STATUS;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public static function getAvailableTypes()
    {
        return self::$values;
    }
}
