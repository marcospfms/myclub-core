import type { Component } from 'vue';
import {
    Award,
    Flag,
    Flame,
    Goal,
    Handshake,
    HeartHandshake,
    Medal,
    Shield,
    ShieldCheck,
    Sparkles,
    Star,
    Target,
    Trophy,
    Zap,
} from 'lucide-vue-next';

export const badgeIconMap: Record<string, Component> = {
    award: Award,
    flag: Flag,
    flame: Flame,
    goal: Goal,
    handshake: Handshake,
    heart_handshake: HeartHandshake,
    medal: Medal,
    shield: Shield,
    shield_check: ShieldCheck,
    sparkles: Sparkles,
    star: Star,
    target: Target,
    trophy: Trophy,
    zap: Zap,
};
