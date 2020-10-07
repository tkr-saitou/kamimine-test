TYPE=VIEW
query=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`kamimine_db`.`t_sbt_busstop` `bst` left join `kamimine_db`.`t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `kamimine_db`.`t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `kamimine_db`.`t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
md5=37b7f60aa75f4ab20961f1092f40bfa9
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=1
with_check_option=0
timestamp=2020-10-07 02:56:38
create-version=1
source=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`t_sbt_busstop` `bst` left join `t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
client_cs_name=utf8
connection_cl_name=utf8_general_ci
view_body_utf8=select `rot`.`buscompany_id` AS `buscompany_id`,`bst`.`busstop_id` AS `busstop_id`,`rot`.`buscategory_cd` AS `buscategory_cd`,count(0) AS `count` from (((`kamimine_db`.`t_sbt_busstop` `bst` left join `kamimine_db`.`t_sbt_busdia` `dia` on((`bst`.`busstop_id` = `dia`.`busstop_id`))) left join `kamimine_db`.`t_sbt_busbin` `bin` on(((`dia`.`buscompany_id` = `bin`.`buscompany_id`) and (`dia`.`bin_no` = `bin`.`bin_no`)))) left join `kamimine_db`.`t_sbt_route` `rot` on(((`bin`.`buscompany_id` = `rot`.`buscompany_id`) and (`bin`.`route_id` = `rot`.`route_id`)))) group by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd` order by `rot`.`buscompany_id`,`bst`.`busstop_id`,`rot`.`buscategory_cd`
