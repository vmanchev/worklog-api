-- no query parameters are provided
select * from log l 
  where 
    l.user_id = AUTH_USER_ID

-- only user_id is provided
select * from log l 
  where 
    l.user_id = GET_USER_ID and 
    l.project_id in (
      select project_id from project_team pt 
        where pt.user_id = AUTH_USER_ID
      );

-- only project_id is provided
select * from log l 
  where 
    l.project_id = GET_PROJECT_ID and 
    l.project_id in (
      select project_id from project_team pt 
        where pt.user_id = AUTH_USER_ID
      );

-- both user_id and project_id are provided
select * from log l 
  where 
    l.user_id = GET_USER_ID and 
    l.project_id = GET_PROJECT_ID
    l.project_id in (
      select project_id from project_team pt 
        where pt.user_id = AUTH_USER_ID
      );
