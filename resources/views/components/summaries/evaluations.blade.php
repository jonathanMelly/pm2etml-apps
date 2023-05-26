@push('custom-scripts')
    @once
        <script type="text/javascript" src="{{ URL::asset ('js/echarts.min.js') }}"></script>
    @endonce
@endpush

<div id="evolutionCharts" class="w-[100%]">
</div>
<script type="text/javascript">
    const allJsonData = {!! $summary !!};

    const datesWindow=allJsonData["datesWindow"];

    const evaluationsData = allJsonData["evaluations"];
    const groups = Object.keys(evaluationsData);

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
        yAxis: 80,
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

    const chartHeight=287;//in px

    groups.forEach((groupName)=>{

        const groupData = evaluationsData[groupName];
        const students = Object.keys(groupData);

        {{-- Build students informations --}}
        students.forEach((student)=>{

            const studentSeriesData = groupData[student];
            {{-- compute markers to identify succes/failure points --}}
            const markPointData=[];

            studentSeriesData.forEach((pointData)=>{

                {{-- success --}}
                let color = '#30cc61';
                let rotate=0;
                {{-- win percentage=0 -> FAILURE --}}
                if(pointData[PI_CURRENT_PERCENTAGE]==0){
                    color='#ec7671';
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

        legends.push(
            (students.length<=1)?{show:false}:{
                data: students,
                orient:'horizontal',
                formatter: function (name) {
                    return 'Legend ' + name;
                }
            },
        );
        visualMaps.push(
            (students.length>1)?{}:
                { // the first visualMap component
                    type: 'piecewise', // defined to be continuous visualMap
                    //seriesIndex: 0,
                    dimension:1,
                    color:['#30cc61','#ec7671'],
                    min:0,
                    max:100,
                    show:false
                },
        );

        chartsIndex++;

    });


    const chartCounts = chartsIndex+1;
    const availableSpace=100;
    const offsetForTitle = 10;
    const spaceBetweenCharts = 13;
    const availableForAllCharts = availableSpace-(spaceBetweenCharts*chartCounts-1);
    const singleHeight = availableForAllCharts/chartCounts;

    {{-- Place elements on grid --}}
    grids.forEach(function (grid, idx) {
        grid.left='3%';

        grid.width='90%';

        grid.height= `${singleHeight}%`;
        grid.top = `${(idx*(singleHeight+spaceBetweenCharts))+offsetForTitle}%`;

        titles[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)/2}%`;
        titles[idx].top = `${parseFloat(grid.top)-7}%`;

        legends[idx].top = `${parseFloat(grid.top)-spaceBetweenCharts/3}%`;
        legends[idx].width = `${parseFloat(grid.width)}%`;
        legends[idx].left = `${parseFloat(grid.left)}%`;
        //legends[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)+2}%`;

    });

    // Specify the configuration items and data for the chart
    let summariesChartOption = {
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
                    `{{__('Date')}}: ${pointData[PI_DATE]}<br />`;

                {{-- Only if not multiple points...--}}
                if(params.length ==1){
                    return globalInfo +
                        `{{__('Result')}}: ${pointData[PI_SUCCESS_TIME]}/${pointData[PI_TIME]}<br />`+
                        `{{__('Summary')}}: ${pointData[PI_CURRENT_PERCENTAGE]}%`;
                }

                //TODO compile infos for same point...

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
    let summariesChart = echarts.init(document.getElementById('evolutionCharts'), '{{\App\Http\Middleware\Theme::isDark(session('theme'))?'dark':'light'}}', {
        //width: '100%',
        height: chartHeight*groups.length
    });
    summariesChart.setOption(summariesChartOption);

    window.addEventListener('resize', function() {
        summariesChart.resize(
            {
                //height: chartHeight*groups.length
            }
        );
    });
</script>
