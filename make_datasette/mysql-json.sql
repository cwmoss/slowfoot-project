select json_object(
    '_id', CONCAT('e-',id), 
    '_type', 'entry', 
    'title', title,
    'created_at', creation,
    'modified_at', lastedit,
    'created_by', edi_id,
    'category', json_object('_ref', str_id),
    'status', status,
    'text', text
    ) 

from blog_entries 
where blogid='2e462844b1ef1f45edc78e001cc6549a'
-- limit 3
;

--
-- cat mysql-json.sql | mysql --silent -uroot alotta  