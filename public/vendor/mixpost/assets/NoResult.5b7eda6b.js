import{l as _}from"./helpers.73aae10f.js";import{E as f}from"./ExclamationCircle.b903b7c6.js";import{_ as g,o as a,c as r,b as s,h as i,g as m,a as l,f as o,t as n,n as d,E as v,F as x,d as p}from"./app.986b6adc.js";const y={},w={xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",fill:"currentColor",class:"w-6 h-6"},$=s("path",{d:"M4.5 4.5a3 3 0 00-3 3v9a3 3 0 003 3h8.25a3 3 0 003-3v-9a3 3 0 00-3-3H4.5zM19.94 18.75l-2.69-2.69V7.94l2.69-2.69c.944-.945 2.56-.276 2.56 1.06v11.38c0 1.336-1.616 2.005-2.56 1.06z"},null,-1),b=[$];function V(e,t){return a(),r("svg",w,b)}const k=g(y,[["render",V]]),E={class:"relative"},C={key:0,class:"absolute top-0 right-0 mt-1 mr-1"},N={key:1,class:"text-center"},z={class:"mt-xs"},B={class:"mt-xs text-red-500"},H=["src"],P={__name:"MediaFile",props:{media:{type:Object,required:!0},imgHeight:{type:String,default:"full"}},setup(e){const t=e,c=i(()=>({full:"h-full",sm:"h-20"})[t.imgHeight]),u=i(()=>_.exports.startsWith(t.media.mime_type,"video"));return(h,j)=>(a(),r("figure",E,[m(h.$slots,"default"),s("div",{class:d(["relative flex rounded",{"border border-red-500 p-5":e.media.hasOwnProperty("error")}])},[u.value?(a(),r("span",C,[l(k,{class:"!w-4 !h-4 text-white"})])):o("",!0),e.media.hasOwnProperty("error")?(a(),r("div",N,[l(f,{class:"w-8 h-8 mx-auto text-red-500"}),s("div",z,n(e.media.name),1),s("div",B,n(e.media.error?e.media.error:"Error uploading file!"),1)])):o("",!0),s("img",{src:e.media.thumb_url,loading:"lazy",alt:"Image",class:d(["w-auto object-cover rounded-md",c.value])},null,10,H)],2)]))}},I={class:"flex items-center"},S={class:"w-8 h-8 bg-cyan-100 text-cyan-600 rounded-full flex items-center justify-center mr-xs"},T={__name:"NoResult",setup(e){return(t,c)=>(a(),r("div",I,[s("div",S,[l(v)]),s("div",null,[t.$slots.default?m(t.$slots,"default",{key:0}):o("",!0),t.$slots.default?o("",!0):(a(),r(x,{key:1},[p("There are no results.")],64))])]))}};export{P as _,T as a};
