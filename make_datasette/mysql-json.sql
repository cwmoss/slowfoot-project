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
where blogid='22e1c726a6b9b1fd782b752c63d7b265'
-- limit 3
;

--
-- cat mysql-json.sql | mysql --silent -uroot alotta  

-- 22e1c726a6b9b1fd782b752c63d7b265
-- ella 2e462844b1ef1f45edc78e001cc6549a


select json_object(
    '_id', CONCAT('e-',id), 
    '_type', 'entry', 
    'title', title,
    'created_at', creation,
    'modified_at', lastedit,
    'created_by', edi_id,
    'category', (SELECT  json_object('descr', descr, 'parent', parent, 'seq', seq) from blog_categories c WHERE c.id = e.str_id AND c.blogid = '22e1c726a6b9b1fd782b752c63d7b265'),
    'files', (SELECT json_arrayagg(
        json_object('_id', id, name', name, 'origname', origname, 'position', ff.seq)
    ) from blog_files f, blog_entry_file ff WHERE ff.eid = e.id AND ff.fid = f.id order by ff.seq),
    'status', status,
    'text', text
    ) 

from blog_entries e 
where blogid='22e1c726a6b9b1fd782b752c63d7b265'
limit 10


(SELECT json_arrayagg(
        json_object(descr, parent, seq)
    ) from blog_categories c WHERE c.ID = e.str_id AND c.blogid = '22e1c726a6b9b1fd782b752c63d7b265'),