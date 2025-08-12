<?php

use yii\db\Migration;

class m250812_144105_init extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('organizations', [
            'id' => 'BIGINT PRIMARY KEY AUTO_INCREMENT',
            'name' => $this->string()->notNull()
        ]);

        $this->createTable('addresses_src', [
            'id' => 'BIGINT PRIMARY KEY AUTO_INCREMENT',
            'organization_id' => $this->bigInteger()->null(),
            'address' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull()
        ], 'ENGINE=InnoDB');

        $this->createIndex('idx_address', 'addresses_src', 'address(255)');
        $this->getDb()
            ->createCommand('CREATE FULLTEXT INDEX idx_fulltext_address ON `addresses_src`(address)')
            ->execute();

        $this->addForeignKey(
            'fk_addresses_src_to_organization',
            'addresses_src',
            'organization_id',
            'organizations',
            'id',
            'CASCADE',
            'CASCADE');

        $this->createTable('addresses_ref', [
            'id' => 'BIGINT PRIMARY KEY AUTO_INCREMENT',
            'organization_id' => $this->bigInteger()->null(),
            'address' => $this->text()->notNull(),
            'src_id' => $this->bigInteger()->null(),
            'match_type' => "ENUM('unmatched', 'auto', 'manual') DEFAULT 'manual'",
            'match_score' => $this->tinyInteger()->null(),
            'updated_at' => $this->dateTime()->notNull()
        ]);

        $this->addForeignKey(
            'fk_addresses_ref_to_organization',
            'addresses_ref',
            'organization_id',
            'organizations',
            'id',
            'CASCADE',
            'CASCADE');

        $this->addForeignKey(
            'fk_addresses_ref_to_addresses_src',
        'addresses_ref',
        'src_id',
        'addresses_src',
        'id',
        'CASCADE',
        'CASCADE');

        $this->createIndex('idx_addresses_src', 'addresses_ref', 'src_id', true);

        $this->getDb()
            ->createCommand('CREATE FULLTEXT INDEX idx_fulltext_address ON `addresses_ref`(address)')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('addresses_ref');
        $this->dropTable('addresses_src');
        $this->dropTable('organizations');
    }
}
