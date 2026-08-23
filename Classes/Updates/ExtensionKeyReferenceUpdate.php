<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Beim Zusammenlegen von blog und t3bootstrap_blog zu ws_blog aendern sich die
 * Extension-Pfade. In der Datenbank stecken solche Pfade dort, wo Redakteure oder
 * aeltere Konfiguration sie hinterlassen haben: FlexForms, TSconfig, TypoScript-
 * Datensaetze, Backend-Layouts.
 *
 * Bewusst NICHT angefasst werden CType, list_type, Tabellennamen und
 * plugin.tx_blog: Der Extbase-Extensionname bleibt "Blog", damit bestehende
 * Inhaltselemente unveraendert weiterlaufen.
 */
#[UpgradeWizard(ExtensionKeyReferenceUpdate::class)]
final class ExtensionKeyReferenceUpdate implements UpgradeWizardInterface
{
    /**
     * Alte Extension-Pfade und ihre Entsprechung. Reihenfolge egal, die Ersetzung
     * ist fuer jeden Wert unabhaengig.
     */
    private const WIZARD_NAMESPACE = 'WapplerSystems\\Blog\\Updates\\';

    private const REPLACEMENTS = [
        'EXT:t3bootstrap_blog/' => 'EXT:ws_blog/',
        'EXT:blog/' => 'EXT:ws_blog/',
        'EXT:blog:' => 'EXT:ws_blog:',
    ];

    /**
     * Spalten, in denen Extension-Pfade vorkommen koennen.
     *
     * @var array<string, string[]>
     */
    private const COLUMNS = [
        'tt_content' => ['pi_flexform'],
        'pages' => ['TSconfig', 'tsconfig_includes'],
        'sys_template' => ['config', 'constants', 'include_static_file'],
        'backend_layout' => ['config', 'icon'],
    ];

    public function getIdentifier(): string
    {
        return self::class;
    }

    public function getTitle(): string
    {
        return 'EXT:ws_blog: Rewrite stored EXT:blog and EXT:t3bootstrap_blog references';
    }

    public function getDescription(): string
    {
        return 'Ersetzt in FlexForms, TSconfig, TypoScript-Datensaetzen und Backend-Layouts'
            . ' die Pfade EXT:blog/ und EXT:t3bootstrap_blog/ durch EXT:ws_blog/.'
            . ' CType, list_type und plugin.tx_blog bleiben unveraendert.';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        foreach ($this->collectRows() as $row) {
            return true;
        }

        return $this->collectStaleWizardFlags() !== [];
    }

    public function executeUpdate(): bool
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        foreach ($this->collectRows() as [$table, $column, $uid, $value]) {
            $connectionPool->getConnectionForTable($table)->update(
                $table,
                [$column => $this->rewrite($value)],
                ['uid' => $uid]
            );
        }

        $registry = GeneralUtility::makeInstance(Registry::class);
        foreach ($this->collectStaleWizardFlags() as $oldIdentifier => $newIdentifier) {
            $registry->set('installUpdate', $newIdentifier, $registry->get('installUpdate', $oldIdentifier));
        }

        return true;
    }

    /**
     * Die Wizards des Forks heissen jetzt WapplerSystems\Blog\Updates\*, in sys_registry
     * stehen sie aber unter ihrem alten Namen als erledigt. Ohne diese Uebernahme bietet
     * der Installer sie erneut an - und ListTypeMigration wuerde bereits migrierte Plugins
     * ein zweites Mal anfassen.
     *
     * @return array<string, string> alter Bezeichner => neuer Bezeichner
     */
    private function collectStaleWizardFlags(): array
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $flags = [];

        foreach (glob(__DIR__ . '/*.php') ?: [] as $file) {
            $className = self::WIZARD_NAMESPACE . basename($file, '.php');
            if ($className === self::class || !class_exists($className)) {
                continue;
            }
            $oldIdentifier = str_replace('WapplerSystems\\Blog\\', 'T3G\\AgencyPack\\Blog\\', $className);
            if ($registry->get('installUpdate', $oldIdentifier) === null) {
                continue;
            }
            if ($registry->get('installUpdate', $className) !== null) {
                continue;
            }
            $flags[$oldIdentifier] = $className;
        }

        return $flags;
    }

    /**
     * Liefert alle Zeilen, die noch einen alten Pfad tragen. Tabellen oder Spalten,
     * die es in dieser Installation nicht gibt, werden uebersprungen - die Liste
     * deckt mehrere TYPO3-Setups ab.
     *
     * @return \Generator<array{0: string, 1: string, 2: int, 3: string}>
     */
    private function collectRows(): \Generator
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        foreach (self::COLUMNS as $table => $columns) {
            $connection = $connectionPool->getConnectionForTable($table);
            $schemaManager = $connection->createSchemaManager();
            if (!$schemaManager->tablesExist([$table])) {
                continue;
            }
            $existingColumns = array_map(
                static fn ($column) => $column->getName(),
                $schemaManager->listTableColumns($table)
            );

            foreach ($columns as $column) {
                if (!in_array(strtolower($column), array_map('strtolower', $existingColumns), true)) {
                    continue;
                }

                $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
                $queryBuilder->getRestrictions()->removeAll();
                $queryBuilder
                    ->select('uid', $column)
                    ->from($table);

                $constraints = [];
                foreach (array_keys(self::REPLACEMENTS) as $needle) {
                    $constraints[] = $queryBuilder->expr()->like(
                        $column,
                        $queryBuilder->createNamedParameter('%' . $queryBuilder->escapeLikeWildcards($needle) . '%')
                    );
                }
                $queryBuilder->where($queryBuilder->expr()->or(...$constraints));

                $result = $queryBuilder->executeQuery();
                while ($row = $result->fetchAssociative()) {
                    yield [$table, $column, (int)$row['uid'], (string)$row[$column]];
                }
            }
        }
    }

    private function rewrite(string $value): string
    {
        return str_replace(array_keys(self::REPLACEMENTS), array_values(self::REPLACEMENTS), $value);
    }
}
