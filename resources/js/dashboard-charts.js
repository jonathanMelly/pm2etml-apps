import * as echarts from 'echarts/core';
window.echarts = echarts;

// Import bar charts, all suffixed with Chart
import { BarChart,PieChart,LineChart } from 'echarts/charts';

// Import the title, tooltip, rectangular coordinate system, dataset and transform components
import {
    TitleComponent,
    TooltipComponent,
    GridComponent,
    //DatasetComponent,
    //TransformComponent,
    VisualMapComponent,
    LegendComponent,
    MarkLineComponent,
    MarkPointComponent,
    DataZoomComponent,

} from 'echarts/components';

// Features like Universal Transition and Label Layout
//import { LabelLayout, UniversalTransition } from 'echarts/features';

// Import the Canvas renderer
// Note that including the CanvasRenderer or SVGRenderer is a required step
import { CanvasRenderer } from 'echarts/renderers';

// Register the required components
echarts.use([
    BarChart,
    PieChart,
    LineChart,
    TitleComponent,
    TooltipComponent,
    GridComponent,
    //DatasetComponent,
    //TransformComponent,
    //LabelLayout,
    //UniversalTransition,
    CanvasRenderer,
    VisualMapComponent,
    LegendComponent,
    MarkLineComponent,
    MarkPointComponent,
    DataZoomComponent
]);


const red = '#ec7671';
const green = '#30cc61';
const yellow = '#dce716';

const colors = {
    'red': red, 'green': green, 'yellow': yellow,
}

function prepareSummariesChartData(allJsonData, successRequirement, labels) {
    const summariesData = allJsonData["summaries"];
    const groupsForSummary = Object.keys(summariesData);
    let studentsCount = 0;

    const summarySeries = [];
    const summaryTitles = [];


    const groupRadius = 100;
    const oneLineHeight = groupRadius * 2;
    const initialSummaryTop = 5;
    let summaryTop = initialSummaryTop;
    const groupWidth = groupRadius * 2;
    const studentsTotalWidth = 600;
    const studentsPack = 8;
    const studentWidth = studentsTotalWidth / studentsPack;
    const horizontalSpacer1 = 10;//space between group and first student
    const horizontalSpacer2 = 50;//space between students

    const verticalSpacer1 = 70;//y space between students

    const studentNameTitle = 15;//y space for student title

    let maxTop = 0;

    groupsForSummary.forEach((groupName, idxGroup) => {

        const studentsData = summariesData[groupName];
        const students = Object.keys(studentsData);
        let left = 0;

        students.forEach((student, idxStudent) => {

            const studentSummary = summariesData[groupName][student];

            let height = oneLineHeight;
            let width = groupWidth;

            let title = groupName;
            let radius = groupRadius * 0.9;
            let top = summaryTop;
            let topTitle = summaryTop;

            let isGroup = true;
            //All is the first Pie which summaries all the students of the group
            if (student !== "all") {
                isGroup = false;
                radius *= 0.6;
                height = oneLineHeight;
                width = studentWidth;

                title = student.limit(10);

                studentsCount++;

                //first line of students, move x
                if (idxStudent === 1) {
                    left += groupWidth + horizontalSpacer1;
                    summaryTop += studentNameTitle;
                    top += studentNameTitle;
                    topTitle = top;
                }//Move student top for second line ? (-1 for the group chart)
                else if (idxStudent !== 1 && (idxStudent - 1) % studentsPack === 0) {

                    summaryTop += oneLineHeight / 2 + verticalSpacer1;

                    top = summaryTop;
                    topTitle = summaryTop;
                    left = groupWidth + horizontalSpacer1;


                }//move student to next x
                else {
                    left += studentWidth + horizontalSpacer2;
                }

            }
            //Group
            else {
                //Adjust because x,y of pie is in the center :-(
                top += groupsForSummary.length > 1 ? groupRadius : groupRadius / 2;//move group chart to center of student small charts

                maxTop = top;
            }

            //console.log(`${groupName}-${student}: top=${summaryTop}px, left=${left}px, radius=${radius}`);

            let total = studentSummary[0] + studentSummary[2];
            const successRatio = studentSummary[0] / total;
            const isFailing = successRatio < successRequirement;
            let successColor = colors.yellow;
            if (isGroup || !isFailing) {
                successColor = colors.green;
            }
            //const difference = Math.round(studentSummary[0]- Math.round(total*successRequirement));

            let target = 0;
            if (isFailing) {
                let a = studentSummary[0];
                let b = total;
                let c = successRequirement * 100;
                let d = 100;
                //Chatgpt: So, the generic formula to solve the equation (a + x) / (b + x) = c / d is:   x = (cb - ad) / (d - c)
                target = Math.round((c * b - a * d) / (d - c));
            }

            summaryTitles.push({
                text: title,
                subtext: `${Math.round(successRatio * 100)}%` + (isGroup || !isFailing ? `` : ` (-${target}p)`),
                top: topTitle + 'px',
                left: left + 'px',
            });


            //console.log(successColor);
            summarySeries.push({
                type: 'pie',
                radius: [radius, '15%'],
                top: top + 'px',
                height: height,
                left: left,
                width: width,
                label: {
                    //fontWeight:'bold',
                    position: 'inside', formatter: function (data) {
                        let isGroup = data.data['isGroup'];
                        if (isGroup) {
                            return `${data['value']} ${labels.students}`;
                        }
                        return `${data.data['value']}p\n(${data.data['details'].length} pr.)`;
                    },
                },
                color: [successColor, colors.red],
                data: [{
                    name: 'ok' + groupName + student,
                    value: studentSummary[0],
                    details: studentSummary[1],
                    total: total,
                    isGroup: isGroup
                }, {
                    name: 'ko' + groupName + student,
                    value: studentSummary[2],
                    details: studentSummary[3],
                    total: total,
                    isGroup: isGroup
                }], /*
                itemStyle: {
                    borderRadius: 1,
                    borderColor: '#9d9999',
                    borderWidth: 1
                },*/
            });

        });
        summaryTop += oneLineHeight;

        //when not many students, second line is empty, we have to fake this emptiness
        let only1Line = students.length - 1 /*-1: In students, there is the ALL special entry /!\*/ <= studentsPack;
        if (only1Line) {
            summaryTop += oneLineHeight / 2 + verticalSpacer1;
        }

    });

    //For students, only show himself in the big graph
    if (summarySeries.length === 2) {
        summaryTitles[0].text = summaryTitles[1].text;
        summaryTitles[0].subtext = summaryTitles[1].subtext;
        summaryTitles.pop();

        summarySeries[0].data = summarySeries[1].data;
        summarySeries[0].color = summarySeries[1].color;
        summarySeries.pop();
    }

    const summariesChartOption = {
        title: summaryTitles, series: summarySeries, tooltip: {
            trigger: 'item', formatter: function (data) {
                return data['data']['details'].join(',');
            }
        },

    };
    return {initialSummaryTop, summaryTop, summariesChartOption};
}

function prepareEvaluationChartsData(allJsonData, successRatio, labels) {
    const evaluationsData = allJsonData["evaluations"];
    const datesWindow = allJsonData["datesWindow"];
    const groupsForEvaluation = Object.keys(evaluationsData);

    // 0=date, 1=successTime, 2=percentage, 3=successTime, 4=totalTime, 5=project name
    let pi = 0;
    const PI_DATE = pi++;
    const PI_CURRENT_PERCENTAGE = pi++;
    const PI_SUCCESS_TIME = pi++;
    const PI_TIME = pi++;
    const PI_TOTAL_SUCCESS_TIME = pi++;
    const PI_TOTAL_TIME = pi++;
    const PI_PROJECT_NAME = pi++;
    const PI_CLIENTS = pi++;

    // 80% required to pass
    let markLine = {
        //name: "min",
        yAxis: successRatio * 100, symbol: 'none', //startpoint
        emphasis: {
            disabled: true
        }, label: {

                show: false,

        }, lineStyle: {

                color: "#53c048"

        },
    };

    const grids = [];
    const xAxes = [];
    const yAxes = [];
    const series = [];
    const titles = [];
    const legends = [];
    const visualMaps = [];
    let chartsIndex = 0;

    const successes = [];

    groupsForEvaluation.forEach((groupName) => {

        const groupData = evaluationsData[groupName];
        const students = Object.keys(groupData);


        // Build students information
        let studentLatestSuccessPercentage = 0;
        students.forEach((student) => {

            const studentSeriesData = groupData[student];
            // compute markers to identify succes/failure points
            const markPointData = [];

            studentSeriesData.forEach((pointData) => {

                // success
                let color = colors.green;
                let rotate = 0;
                // win percentage=0 -> FAILURE
                if (pointData[PI_SUCCESS_TIME] == 0) {
                    color = colors.red;
                    rotate = 180;
                }

                // Markpoints show red/green bulb on eval to show failure/success
                markPointData.push({
                    name: 'name',
                    value: '',
                    xAxis: pointData[PI_DATE],
                    yAxis: pointData[PI_CURRENT_PERCENTAGE],
                    itemStyle: {color: color},
                    emphasis: {disabled: true},
                    symbol: 'pin',
                    symbolSize: 20,
                    symbolRotate: rotate,
                });

                studentLatestSuccessPercentage = pointData[PI_CURRENT_PERCENTAGE];
            });

            successes[student] = studentLatestSuccessPercentage >= successRatio * 100;


            // Add markline for each student in case of manual selection
            series.push({
                name: student, data: studentSeriesData, type: 'line', ...(markLine != null) && {
                    markLine: {
                        symbol: 'arrow', data: [markLine]
                    }
                }, markPoint: {
                    data: markPointData
                }, lineStyle: {
                    type: 'dotted', cap: 'round'
                }, xAxisIndex: chartsIndex, yAxisIndex: chartsIndex, ...(students.length === 1) && {
                    endLabel: {
                        show: true, formatter: function (params) {
                            return params.seriesName;
                        }
                    }
                }
            });

        });

        grids.push({
            show: true, borderWidth: 2, shadowColor: 'rgba(0, 0, 0, 0.3)', shadowBlur: 2
        });

        xAxes.push({
            type: 'time', gridIndex: chartsIndex, min: datesWindow[0], max: datesWindow[1]
        });
        yAxes.push({
            type: 'value', max: 100, gridIndex: chartsIndex
        });

        titles.push({
            //left:'center',
            textAlign: 'center', text: groupName, textStyle: {
                fontSize: 12, //fontWeight: 'normal'
            }
        });

        legends.push((students.length <= 1) ? {show: false} : {
            data: students.map((stud) => new Object({
                name: stud,
                icon: successes[stud] ? '' : 'triangle',
                itemStyle: {borderColor: colors.red, borderWidth: successes[stud] ? 0 : 2}
            })), orient: 'horizontal', formatter: function (name) {
                return name.limit(15);
            }
        },);
        visualMaps.push((students.length > 1) ? {} : { // the first visualMap component
            type: 'piecewise', // defined to be continuous visualMap
            //seriesIndex: 0,
            dimension: 1, color: [colors.green, colors.red], min: 0, max: 100, show: false
        },);

        chartsIndex++;

    });


    const chartCounts = chartsIndex + 1;
    const offsetForMainTitle = 10;
    const spaceBetweenCharts = 15;
    const availableForAllCharts = 100 - (spaceBetweenCharts * chartCounts - 1);
    const singleHeight = availableForAllCharts / chartCounts;

    let gridTop = offsetForMainTitle;

    // Place elements on grid
    grids.forEach(function (grid, idx) {
        grid.left = '3%';

        grid.width = '90%';

        grid.height = `${singleHeight}%`;
        grid.top = `${gridTop}%`;

        titles[idx].left = `${parseFloat(grid.left) + parseFloat(grid.width) / 2}%`;
        titles[idx].top = `${parseFloat(grid.top) - 8}%`;

        legends[idx].top = `${parseFloat(grid.top) - spaceBetweenCharts / 2.5}%`;
        legends[idx].width = `${parseFloat(grid.width)}%`;
        legends[idx].left = `${parseFloat(grid.left)}%`;
        //legends[idx].left = `${parseFloat(grid.left)+parseFloat(grid.width)+2}%`;

        gridTop += singleHeight + spaceBetweenCharts;

    });

    // Specify the configuration items and data for the chart
    let evolutionChartOption = {
        title: titles.concat([{
            text: labels['evolution'], top: 0, left: 0
        }]), grid: grids, xAxis: xAxes, yAxis: yAxes, series: series, //series: series,

        legend: legends, visualMaps: visualMaps,

        tooltip: {
            trigger: "axis", formatter: function (params) {

                const pointData = params[0].data;// with axis trigger adds an array dimension for multiple y on same x...
                const globalInfo = `${labels.projectName}: <b>${pointData[PI_PROJECT_NAME]}</b> (${pointData[PI_CLIENTS]})<br />` + `${labels.date}: ${new Date(pointData[PI_DATE]).toLocaleDateString()}<br />`;

                //Only if not multiple points ...
                if (params.length === 1) {
                    return `${params[0].seriesName}<br />` + globalInfo + `${labels.result}: ${pointData[PI_SUCCESS_TIME]}/${pointData[PI_TIME]}p<br />` + `${labels.summary}: ${pointData[PI_CURRENT_PERCENTAGE]}%`;
                }

                let compiledInfos = {success: [], failure: []};

                params.forEach(function (data) {
                    const studentName = data.seriesName;
                    const pointData = data.data;
                    const ok = pointData[PI_SUCCESS_TIME] > 0;
                    compiledInfos[ok ? "success" : "failure"].push(studentName);

                });

                return globalInfo + '<br/>ok:' + compiledInfos["success"].join('<br />') + '<br/><br/>ko:' + compiledInfos["failure"].join('<br />');

            },

        }, dataZoom: {
            type: 'slider',
            xAxisIndex: grids.map((_, i) => i),
            startValue: datesWindow[2],
            endValue: datesWindow[3],
            top: parseFloat(grids[grids.length - 1].top) + parseFloat(grids[grids.length - 1].height) + 4 + (grids.length === 1 ? 10 : 0) + '%'
        },

    };
    return {groupsForEvaluation, evolutionChartOption};
}

window.prepareSummariesChartData = prepareSummariesChartData;
window.prepareEvaluationChartsData = prepareEvaluationChartsData;
