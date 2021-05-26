select json_object(
    '_id', CONCAT('e-',id), 
    '_type', 'entry', 
    'title', title,
    'created_at', creation,
    'modified_at', lastedit,
    'created_by', edi_id,
    'category', (SELECT  json_object('descr', descr, 'parent', parent, 'seq', seq) from blog_categories c WHERE c.id = e.str_id AND c.blogid = '22e1c726a6b9b1fd782b752c63d7b265'),
    'files', (SELECT json_arrayagg(
        json_object('_id', id, 'name', name, 'origname', origname, 'position', ff.seq, 'ext', ext)
    ) from blog_files f, blog_entry_file ff WHERE ff.eid = e.id AND ff.fid = f.id order by ff.seq),
    'status', status,
    'author', (SELECT json_object('name', uname, 'lname', lname, 'fname', fname, 'email', email) from blog_editors ed WHERE ed.id = e.edi_id),
    'text', text
    ) 

from blog_entries e 
where blogid='331554b408d9aad2bdc71c674836055a'
order by creation desc

-- yba

-- larsramberg '22e1c726a6b9b1fd782b752c63d7b265'


