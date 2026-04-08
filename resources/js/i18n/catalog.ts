import { badgeMessagesEn } from '@/i18n/locales/en/badges';
import { positionMessagesEn } from '@/i18n/locales/en/positions';
import { sportModeMessagesEn } from '@/i18n/locales/en/sportModes';
import { staffRoleMessagesEn } from '@/i18n/locales/en/staffRoles';
import { badgeMessagesPtBr } from '@/i18n/locales/pt-BR/badges';
import { positionMessagesPtBr } from '@/i18n/locales/pt-BR/positions';
import { sportModeMessagesPtBr } from '@/i18n/locales/pt-BR/sportModes';
import { staffRoleMessagesPtBr } from '@/i18n/locales/pt-BR/staffRoles';

export type AdminLocale = 'en' | 'pt-BR';

const catalogMessages = {
    en: {
        badges: badgeMessagesEn,
        positions: positionMessagesEn,
        sport_modes: sportModeMessagesEn,
        staff_roles: staffRoleMessagesEn,
    },
    'pt-BR': {
        badges: badgeMessagesPtBr,
        positions: positionMessagesPtBr,
        sport_modes: sportModeMessagesPtBr,
        staff_roles: staffRoleMessagesPtBr,
    },
} as const;

export function resolveCatalogMessage(locale: AdminLocale, key: string): string {
    const segments = key.split('.');
    let cursor: unknown = catalogMessages[locale];

    for (const segment of segments) {
        if (typeof cursor !== 'object' || cursor === null || !(segment in cursor)) {
            return key;
        }

        cursor = (cursor as Record<string, unknown>)[segment];
    }

    return typeof cursor === 'string' ? cursor : key;
}

export { catalogMessages };
