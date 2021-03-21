select json_object(
    '_id', CONCAT('p-',id), 
    '_type', p.post_type, 
    'title', p.post_title,
    'slug', p.post_name,
    'created_at', p.post_date,
   -- 'modified_at', lastedit,
    'created_by', post_author,
   -- 'category', json_object('_ref', str_id),
    'meta', (SELECT json_arrayagg(
        json_object(meta_key, meta_value)
    ) from wp_postmeta m WHERE p.ID = m.post_id),
    'status', post_status,
    'excerpt', post_excerpt,
    'text', post_content
    ) 

from wp_posts p
where  p.post_status = 'publish'
    AND p.post_type IN('post')
    AND p.post_date < NOW()
order by p.post_date DESC
-- limit 3
;

