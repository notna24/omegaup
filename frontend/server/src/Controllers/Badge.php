<?php

namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @psalm-type Badge=array{assignation_time?: \OmegaUp\Timestamp|null, badge_alias: string, unlocked?: boolean, first_assignation?: \OmegaUp\Timestamp|null, total_users?: int, owners_count?: int}
 * @psalm-type BadgeDetailsPayload=array{badge: Badge}
 */
class Badge extends \OmegaUp\Controllers\Controller {
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is really a string. */
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    /**
     * @return list<string>
     */
    public static function getAllBadges(): array {
        $aliases = array_diff(
            scandir(
                strval(static::OMEGAUP_BADGES_ROOT)
            ),
            ['..', '.', 'default_icon.svg']
        );
        $results = [];
        foreach ($aliases as $alias) {
            if (!is_dir(strval(static::OMEGAUP_BADGES_ROOT) . "/${alias}")) {
                continue;
            }
            $results[] = $alias;
        }
        return $results;
    }

    /**
     * Returns a list of existing badges
     *
     * @return list<string>
     */
    public static function apiList(\OmegaUp\Request $r): array {
        return self::getAllBadges();
    }

    /**
     * Returns a list of badges owned by current user
     *
     * @return array{badges: list<Badge>}
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        return [
            'badges' => is_null($r->user) ?
                [] :
                \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($r->user),
        ];
    }

    /**
     * Returns a list of badges owned by a certain user
     *
     * @omegaup-request-param mixed $target_username
     *
     * @return array{badges: list<Badge>}
     */
    public static function apiUserList(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateValidUsername(
            $r['target_username'],
            'target_username'
        );
        $user = \OmegaUp\DAO\Users::FindByUsername($r['target_username']);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        return [
            'badges' => \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($user),
        ];
    }

    /**
     * Returns a the assignation timestamp of a badge
     * for current user.
     *
     * @omegaup-request-param mixed $badge_alias
     *
     * @return array{assignation_time: \OmegaUp\Timestamp|null}
     */
    public static function apiMyBadgeAssignationTime(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );
        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            self::getAllBadges()
        );
        return [
            'assignation_time' => is_null($r->user) ?
                null :
                \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                    $r->user,
                    $r['badge_alias']
                ),
        ];
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @omegaup-request-param mixed $badge_alias
     *
     * @return Badge
     */
    public static function apiBadgeDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );
        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            self::getAllBadges()
        );
        return self::getBadgeDetails($r['badge_alias']);
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @return Badge
     */
    private static function getBadgeDetails(string $badgeAlias): array {
        $totalUsers = max(\OmegaUp\DAO\Users::getUsersCount(), 1);
        $ownersCount = \OmegaUp\DAO\UsersBadges::getBadgeOwnersCount(
            $badgeAlias
        );
        $firstAssignation = \OmegaUp\DAO\UsersBadges::getBadgeFirstAssignationTime(
            $badgeAlias
        );
        return [
            'badge_alias' => $badgeAlias,
            'first_assignation' => $firstAssignation,
            'total_users' => $totalUsers,
            'owners_count' => $ownersCount,
        ];
    }

    /**
     * @omegaup-request-param mixed $badge_alias
     *
     * @return array{smartyProperties: array{payload: BadgeDetailsPayload, title: string}, entrypoint: string}
     */
    public static function getDetailsForSmarty(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );

        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            \OmegaUp\Controllers\Badge::getAllBadges()
        );
        return [
            'smartyProperties' => [
                'payload' => [
                    'badge' => (
                        self::getBadgeDetails($r['badge_alias']) +
                        [
                            'assignation_time' => is_null($r->user) ?
                                null :
                                \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                                    $r->user,
                                    $r['badge_alias']
                                ),
                        ]
                    ),
                ],
                'title' => 'omegaupTitleBadges'
            ],
            'entrypoint' => 'badge_details',
        ];
    }
}
