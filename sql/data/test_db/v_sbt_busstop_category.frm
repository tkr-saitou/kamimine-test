TYPE=VIEW
query=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`test_db`.`t_sbt_busstop` `bst` left join `test_db`.`t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `test_db`.`t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `test_db`.`t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
md5=f82a2c97cda540bcb13e9c7d97d33349
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=2020-10-08 03:18:42
create-version=1
source=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`t_sbt_busstop` `bst` left join `t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
client_cs_name=utf8
connection_cl_name=utf8_general_ci
view_body_utf8=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`test_db`.`t_sbt_busstop` `bst` left join `test_db`.`t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `test_db`.`t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `test_db`.`t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
