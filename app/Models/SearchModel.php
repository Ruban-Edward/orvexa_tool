<?php

/**
 * SearchModel.php
 *
 * @category   Model
 * @purpose    Global search across products, backlog items, user stories, tasks and
 *             sprints, limited to the products the user is a member of. One UNION
 *             query keeps it to a single round trip per keystroke.
 */

namespace App\Models;

class SearchModel extends BaseModel
{
    protected $table = 'orvexa_product';

    public function search(int $userId, string $term, int $perType = 5): array
    {
        $like  = '%' . $this->db->escapeLikeString($term) . '%';
        $id    = ctype_digit(ltrim($term, '#')) ? (int) ltrim($term, '#') : 0;
        $limit = (int) $perType;
        $mine  = "SELECT r_product_id FROM orvexa_product_user WHERE r_user_id = :user_id: AND is_deleted = 'N'";

        $sql = "(SELECT 'product' AS type, p.external_project_id AS id, p.product_name AS title, '' AS subtitle,
                        p.external_project_id AS product_id, NULL AS backlog_item_id, NULL AS user_story_id
                 FROM orvexa_product p
                 WHERE p.is_deleted = 'N' AND p.external_project_id IN ($mine)
                   AND p.product_name LIKE :like: ESCAPE '!'
                 ORDER BY p.product_name LIMIT $limit)
                UNION ALL
                (SELECT 'backlog', bi.backlog_item_id, bi.backlog_item_name, p.product_name,
                        bi.r_product_id, bi.backlog_item_id, NULL
                 FROM orvexa_backlog_item bi
                 INNER JOIN orvexa_product p ON p.external_project_id = bi.r_product_id
                 WHERE bi.is_deleted = 'N' AND bi.r_product_id IN ($mine)
                   AND (bi.backlog_item_name LIKE :like: ESCAPE '!' OR bi.backlog_item_id = :id:)
                 ORDER BY bi.updated_date DESC LIMIT $limit)
                UNION ALL
                (SELECT 'story', us.user_story_id, CONCAT('As ', us.as_a_an, ', I want ', us.i_want), bi.backlog_item_name,
                        bi.r_product_id, bi.backlog_item_id, us.user_story_id
                 FROM orvexa_user_story us
                 INNER JOIN orvexa_epic e ON e.epic_id = us.r_epic_id
                 INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = e.r_backlog_item_id AND bi.is_deleted = 'N'
                 WHERE us.is_deleted = 'N' AND bi.r_product_id IN ($mine)
                   AND (us.i_want LIKE :like: ESCAPE '!' OR us.as_a_an LIKE :like: ESCAPE '!' OR us.user_story_id = :id:)
                 ORDER BY us.updated_date DESC LIMIT $limit)
                UNION ALL
                (SELECT 'task', t.task_id, t.task_title, bi.backlog_item_name,
                        bi.r_product_id, bi.backlog_item_id, t.r_user_story_id
                 FROM orvexa_task t
                 INNER JOIN orvexa_user_story us ON us.user_story_id = t.r_user_story_id AND us.is_deleted = 'N'
                 INNER JOIN orvexa_epic e ON e.epic_id = us.r_epic_id
                 INNER JOIN orvexa_backlog_item bi ON bi.backlog_item_id = e.r_backlog_item_id AND bi.is_deleted = 'N'
                 WHERE t.is_deleted = 'N' AND bi.r_product_id IN ($mine)
                   AND (t.task_title LIKE :like: ESCAPE '!' OR t.task_id = :id:)
                 ORDER BY t.updated_date DESC LIMIT $limit)
                UNION ALL
                (SELECT 'sprint', s.sprint_id, COALESCE(NULLIF(s.sprint_name, ''), CONCAT('Sprint ', s.sprint_version)), p.product_name,
                        s.r_product_id, NULL, NULL
                 FROM orvexa_sprint s
                 INNER JOIN orvexa_product p ON p.external_project_id = s.r_product_id
                 WHERE s.is_deleted = 'N' AND s.r_product_id IN ($mine)
                   AND (s.sprint_name LIKE :like: ESCAPE '!' OR CONCAT('Sprint ', s.sprint_version) LIKE :like: ESCAPE '!')
                 ORDER BY s.start_date DESC LIMIT $limit)";

        return $this->query($sql, ['user_id' => $userId, 'like' => $like, 'id' => $id])->getResultArray();
    }
}
