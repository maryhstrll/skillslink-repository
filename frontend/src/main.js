import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './css/app.css'
import MessageContainer from './components/MessageContainer.vue'
// Import Lucide icons
import { 
  Menu, Bell, Home, Users, FileText, BarChart3, UserCog, Settings, 
  Save, Shield, Mail, Send, Database, Download, Upload, PieChart, 
  TrendingUp, Plus, Edit, Trash2, Eye, EyeOff, ChevronUp, User, 
  LogOut, RefreshCw, Check, X, UserCheck, FileSpreadsheet, Zap
} from 'lucide-vue-next'

const app = createApp(App);
app.component('MessageContainer', MessageContainer);
// Register Lucide icons as global components
app.component('IconMenu', Menu);
app.component('IconBell', Bell);
app.component('IconHome', Home);
app.component('IconUsers', Users);
app.component('IconFileText', FileText);
app.component('IconBarChart3', BarChart3);
app.component('IconUserCog', UserCog);
app.component('IconSettings', Settings);
app.component('IconSave', Save);
app.component('IconShield', Shield);
app.component('IconMail', Mail);
app.component('IconSend', Send);
app.component('IconDatabase', Database);
app.component('IconDownload', Download);
app.component('IconUpload', Upload);
app.component('IconPieChart', PieChart);
app.component('IconTrendingUp', TrendingUp);
app.component('IconPlus', Plus);
app.component('IconEdit', Edit);
app.component('IconTrash2', Trash2);
app.component('IconEye', Eye);
app.component('IconEyeOff', EyeOff);
app.component('IconChevronUp', ChevronUp);
app.component('IconUser', User);
app.component('IconLogOut', LogOut);
app.component('IconRefreshCw', RefreshCw);
app.component('IconCheck', Check);
app.component('IconX', X);
app.component('IconUserCheck', UserCheck);
app.component('IconFileSpreadsheet', FileSpreadsheet);
app.component('IconZap', Zap);
app.use(router);
app.mount('#app');