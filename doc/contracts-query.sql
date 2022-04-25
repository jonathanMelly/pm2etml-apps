select jd.id,min(c.start) as min_start,date_format(max(c.end),'%d.%m.%Y') as max_end,count(c.id) as contracts_count,min(c.end) from job_definitions jd
	inner join contracts c on c.job_definition_id=jd.id
	inner join contract_client cc on cc.contract_id=c.id and cc.user_id=1
	
	inner join contract_worker cw on cw.contract_id=c.id
		inner join group_members gm on cw.group_member_id=gm.id
			inner join groups g on gm.group_id=g.id
				inner join academic_periods ap on g.academic_period_id=ap.id and ap.id=5
	
	group by c.job_definition_id
	
	order by min(c.`end`) asc