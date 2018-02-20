import {
  parse,
  format,
  differenceInWeeks,
  differenceInHours,
  differenceInCalendarDays,
} from 'date-fns';

export function datetime(ts) {
  const time = parse(1000 * parseInt(ts, 10));
  const now = new Date();
  if (differenceInWeeks(now, time) > 1) {
    return format(time, 'YYYY-MM-DD');
  }
  const days = differenceInCalendarDays(now, time);
  if (days > 1) {
    return `${days} 天前`;
  }

  const hours = differenceInHours(now, time);
  if (hours > 1) {
    return `${hours} 小时前`;
  }

  return '刚才';
}
