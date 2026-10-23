import fallbackIcon from '@/../../public/images/Icon2025black.webp';

export function gameImageUrl(imageId?: string | null): string {
    if (!imageId) return fallbackIcon;

    return /^https?:\/\//i.test(imageId) || /\.(png|jpe?g|webp|svg)(?:[?#].*)?$/i.test(imageId) ? imageId : fallbackIcon;
}
