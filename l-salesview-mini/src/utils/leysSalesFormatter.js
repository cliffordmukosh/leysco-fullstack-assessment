/*** @component: leysSalesFormatter
 * @description: Utility function to format currency in Leysco specific format: KES 10,000.00 /=
 */

export function leysSalesFormatter(value, currency = 'KES') {
  if (value === null || value === undefined || isNaN(value)) {
    return `${currency} 0.00 /=`
  }

  const formatted = new Intl.NumberFormat('en-KE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(Math.abs(value))

  return `${currency} ${formatted} /=`
}