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
