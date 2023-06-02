@push('custom-scripts')
    @once
        <script type="text/javascript" src="{{ URL::asset ('js/echarts.min.js') }}"></script>
    @endonce
@endpush

<div id="summariesCharts" class="w-[100%]">
</div>
<div id="evolutionCharts" class="w-[100%]">
</div>
<script type="text/javascript">

    const theme = '{{\App\Http\Middleware\Theme::isDark(session('theme'))?'dark':'light'}}';

    const red='#ec7671';
    const green='#30cc61';
    const yellow='#dce716';

    const allJsonData = {!! $summary !!};

    const datesWindow=allJsonData["datesWindow"];

    const summariesData = allJsonData["summaries"];
    const groupsForSummary = Object.keys(summariesData);
    let studentsCount =0;

    const summarySeries=[];
    const summaryTitles=[];


    const groupRadius=100;
    const groupHeight = 300;
    let summaryTop=0;
    const groupWidth = groupRadius*2;
    const studentsTotalWidth=800;
    const studentsPack=8;
    const studentWidth = studentsTotalWidth/studentsPack;
    const horizontalSpacer1=25;
    const verticalSpacer1=50;

    groupsForSummary.forEach((groupName, idxGroup)=>{

        const studentsData = summariesData[groupName];
        const students = Object.keys(studentsData);
        let left=0;

        students.forEach((student,idxStudent)=>{

            const studentSummary = summariesData[groupName][student];

            let height=groupHeight;
            let width = groupWidth;

            let title=groupName;
            let radius = groupRadius*0.9;
            let top = summaryTop;
            let topTitle = summaryTop;

            let isGroup=true;
            if(student!="all"){
                isGroup=false;
                radius *=0.75;
                height=groupHeight;
                width=studentWidth;

                title=student;

                studentsCount++;

                //Move student top for second line ? (-1 for the group chart)
                if(idxStudent==1){
                    left+=groupWidth+horizontalSpacer1;
                }else if(idxStudent!=1 && (idxStudent-1)%studentsPack==0){
                    summaryTop+=groupHeight/2+verticalSpacer1;
                    top=summaryTop;
                    topTitle=top;
                    left = groupWidth+horizontalSpacer1;
                }else{
                    left +=studentWidth*1.5;
                }

                //Adjust because x,y of pie is in the center :-(
                top -= groupRadius-radius;
            }
            else{
                //Adjust group chart y
                if(groupsForSummary.length>1) {
                    top += verticalSpacer1;
                }
            }


            //console.log(`${groupName}-${student}: top=${summaryTop}px, left=${left}px, radius=${radius}`);

            let total = studentSummary[0]+studentSummary[2];
            const successPercentage = studentSummary[0]/total;
            const isFailing =successPercentage<{{\App\Services\SummariesService::SUCCESS_REQUIREMENT}};
            let successColor = yellow;
            if(isGroup || !isFailing){
                successColor=green;
            }
            //const difference = Math.round(studentSummary[0]- Math.round(total*{{\App\Services\SummariesService::SUCCESS_REQUIREMENT}}));

            let target=0;
            if(isFailing){
                let a=studentSummary[0];
                let b=total;
                let c={{\App\Services\SummariesService::SUCCESS_REQUIREMENT}}*100;
                let d=100;
                //Chatgpt: So, the generic formula to solve the equation (a + x) / (b + x) = c / d is:   x = (cb - ad) / (d - c)
                target = Math.round((c*b - a*d) / (d - c));
            }

            summaryTitles.push({
                text: title,
                subtext: `${Math.round(successPercentage*100)}%`+ (isGroup||!isFailing?``:` (-${target}p)`),
                top: topTitle + 'px',
                left: left+'px',
            });

            //console.log(successColor);
            summarySeries.push({
                type: 'pie',
                radius:[radius,'15%'],
                top: top + 'px',
                height:height,
                left: left,
                width: width,
                label: {
                    //fontWeight:'bold',
                    position:'inside',
                    formatter: function(data){
                        let isGroup = data.data['isGroup'];
                        if(isGroup){
                            return `${data['value']} élève(s)`;
                        }
                        return `${data.data['value']}p\n(${data.data['details'].length} pr.)`;
                    },
                },
                color:[successColor,red],
                data: [
                    {name:'ok'+groupName+student,value:studentSummary[0],details:studentSummary[1],total:total,isGroup:isGroup},
                    {name:'ko'+groupName+student,value:studentSummary[2],details:studentSummary[3],total:total,isGroup:isGroup}
                ],
                /*
                itemStyle: {
                    borderRadius: 1,
                    borderColor: '#9d9999',
                    borderWidth: 1
                },*/
            });

        });
        summaryTop+=groupHeight;

    });

    //For students, only show himself in the big graph
    if(summarySeries.length==2){
        summaryTitles[0].text = summaryTitles[1].text;
        summaryTitles[0].subtext = summaryTitles[1].subtext;
        summaryTitles.pop();

        summarySeries[0].data = summarySeries[1].data;
        summarySeries[0].color = summarySeries[1].color;
        summarySeries.pop();
    }

    const summariesChartOption = {
        title: summaryTitles,
        series: summarySeries,
        tooltip: {
            trigger: 'item',
            formatter:function(data){return data['data']['details'].join(',');}
        },

    };

    const evaluationsData = allJsonData["evaluations"];
    const groupsForEvaluation = Object.keys(evaluationsData);

    {{-- 0=date, 1=successTime, 2=percentage, 3=successTime, 4=totalTime, 5=project name --}}
    let pi=0;
    const PI_DATE=pi++;
    const PI_CURRENT_PERCENTAGE=pi++;
    const PI_SUCCESS_TIME=pi++;
    const PI_TIME=pi++;
    const PI_TOTAL_SUCCESS_TIME=pi++;
    const PI_TOTAL_TIME=pi++;
    const PI_PROJECT_NAME=pi++;
    const PI_CLIENTS=pi++;

    {{-- 80% required to pass --}}
    let markLine = {
        //name: "min",
        yAxis: {{\App\Services\SummariesService::SUCCESS_REQUIREMENT*100}},
        symbol:'none', //startpoint
        emphasis:{
            disabled: true
        },
        label: {
            normal: {
                show: false,
            }
        },
        lineStyle: {
            normal: {
                color: "#53c048"
            }
        },
    };

    const grids = [];
    const xAxes = [];
    const yAxes = [];
    const series = [];
    const titles = [];
    const legends =[];
    const visualMaps =[];
    const dataZooms = [];
    let chartsIndex = 0;


    groupsForEvaluation.forEach((groupName)=>{

        const groupData = evaluationsData[groupName];
        const students = Object.keys(groupData);

        {{-- Build students informations --}}
        students.forEach((student)=>{

            const studentSeriesData = groupData[student];
            {{-- compute markers to identify succes/failure points --}}
            const markPointData=[];

            studentSeriesData.forEach((pointData)=>{

                {{-- success --}}
                let color = green;
                let rotate=0;
                {{-- win percentage=0 -> FAILURE --}}
                if(pointData[PI_SUCCESS_TIME]==0){
                    color=red;
                    rotate=180;
                }

                {{-- Markpoints show red/green bulb on eval to show failure/success --}}
                markPointData.push({
                    name: 'name',
                    value: '',
                    xAxis: pointData[PI_DATE],
                    yAxis: pointData[PI_CURRENT_PERCENTAGE],
                    itemStyle: {color: color},
                    emphasis: {disabled: true},
                    symbol:'pin',
                    symbolSize:20,
                    symbolRotate:rotate,
                });
            });


            {{-- Add markline for each student in case of manual selection --}}
            series.push({
                name:student,
                data: studentSeriesData,
                type: 'line',
                ...(markLine!=null) && {markLine:{
                        symbol:'arrow',
                        data:[markLine]
                    }},
                markPoint: {
                    data: markPointData
                },
                lineStyle:{
                    type:'dotted',
                    cap:'round'
                },
                xAxisIndex: chartsIndex,
                yAxisIndex: chartsIndex,
                ...(students.length==1) && {endLabel: {
                        show: true,
                        formatter: function (params) {
                            return params.seriesName;
                        }
                    }}
            });

        });

        grids.push({
            show: true,
            borderWidth: 2,
            shadowColor: 'rgba(0, 0, 0, 0.3)',
            shadowBlur: 2
        });

        xAxes.push({
            type: 'time',
            gridIndex: chartsIndex,
            min:datesWindow[0],
            max:datesWindow[1]
        });
        yAxes.push({
            type: 'value',
            max: 100,
            gridIndex: chartsIndex
        });

        titles.push({
            //left:'center',
            textAlign: 'center',
            text: groupName,
            textStyle: {
                fontSize: 12,
                //fontWeight: 'normal'
            }
        });

        const success=Math.random()>0.5;
        legends.push(
            (students.length<=1)?{show:false}:{
                data: students.map((stud)=>new Object({name:stud,icon: success?'':'triangle',itemStyle:{borderColor:red,borderWidth:success?0:1}})),
                orient:'horizontal'
            },
        );
        visualMaps.push(
            (students.length>1)?{}:
                { // the first visualMap component
                    type: 'piecewise', // defined to be continuous visualMap
                    //seriesIndex: 0,
                    dimension:1,
                    color:[green,red],
                    min:0,
                    max:100,
                    show:false
                },
        );

        chartsIndex++;

    });


    const chartCounts = chartsIndex+1;
    const offsetForTitle = 10;
    const spaceBetweenCharts = 7;
    const availableForAllCharts = 100-(spaceBetweenCharts*chartCounts-1);
    const singleHeight = availableForAllCharts/chartCounts;

    {{-- Place elements on grid --}}
    grids.forEach(function (grid, idx) {
        grid.left='3%';

        grid.width='90%';

        grid.height= `${singleHeight}%`;
        grid.top = `${(idx*(singleHeight+spaceBetweenCharts))+offsetForTitle}%`;

        titles[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)/2}%`;
        titles[idx].top = `${parseFloat(grid.top)-4}%`;

        legends[idx].top = `${parseFloat(grid.top)-spaceBetweenCharts/3}%`;
        legends[idx].width = `${parseFloat(grid.width)}%`;
        legends[idx].left = `${parseFloat(grid.left)}%`;
        //legends[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)+2}%`;

    });

    // Specify the configuration items and data for the chart
    let evolutionChartOption = {
        title: titles.concat([
            {
                text: '{{__('Evolution')}}',
                top: 0,
                left: 0
            }
        ]),
        grid: grids,
        xAxis: xAxes,
        yAxis: yAxes,
        series:series,
        //series: series,

        legend:legends,
        visualMaps:visualMaps,

        tooltip: {
            trigger: "axis",
            formatter: function (params) {

                const pointData=params[0].data;{{-- with axis trigger adds an array dimension for multiple y on same x... --}}
                const globalInfo =
                    `{{__('Project')}}: <b>${pointData[PI_PROJECT_NAME]}</b> (${pointData[PI_CLIENTS]})<br />`+
                    `{{__('Date')}}: ${new Date(pointData[PI_DATE]).toLocaleDateString()}<br />`;

                {{-- Only if not multiple points...--}}
                if(params.length ==1){
                    return globalInfo +
                        `{{__('Result')}}: ${pointData[PI_SUCCESS_TIME]}/${pointData[PI_TIME]}p<br />`+
                        `{{__('Current summary')}}: ${pointData[PI_CURRENT_PERCENTAGE]}%`;
                }

                let compiledInfos={success:[],failure:[]};

                params.forEach(function(data){
                   const studentName = data.seriesName;
                   const pointData = data.data;
                   const ok = pointData[PI_SUCCESS_TIME]>0;
                   compiledInfos[ok?"success":"failure"].push(studentName);

                });

                return globalInfo+'<br/>ok:'+compiledInfos["success"].join(',')+'<br/>ko:'+compiledInfos["failure"];

            },

        },
        dataZoom:
            {
                type: 'slider',
                xAxisIndex:grids.map((_,i)=>i),
                startValue: datesWindow[2],
                endValue: datesWindow[3],
                top:parseFloat(grids[grids.length-1].top)+parseFloat(grids[grids.length-1].height)+7+'%'
            },

    };


    //Create chart
    let evolutionChart = echarts.init(document.getElementById('evolutionCharts'), theme, {
        //width: '100%',
        height: 285*groupsForEvaluation.length
    });
    evolutionChart.setOption(evolutionChartOption);

    //Create chart
    let summariesChart = echarts.init(document.getElementById('summariesCharts'), theme, {
        //width: '100%',
        height: groupHeight * groupsForSummary.length
    });
    summariesChart.setOption(summariesChartOption);

    window.addEventListener('resize', function() {
        [evolutionChart,summariesChart].forEach(chart=>chart.resize());
    });
</script>
