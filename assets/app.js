// React components are not used in this project
// registerReactControllerComponents();

import './bootstrap.js';
import './js/swup.js';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { Flip } from 'gsap/Flip';

gsap.registerPlugin(ScrollTrigger, Flip);

console.log('GSAP initialized with ScrollTrigger and Flip');
