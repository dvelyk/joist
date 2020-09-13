/* Lookup meta_values by meta_key. */
SELECT
	post_id, meta_value
FROM wp_postmeta
WHERE
	meta_key LIKE '%' ORDER BY post_id

/* Get all postmeta meta_keys for a particular post_type. */
SELECT DISTINCT
	meta.meta_key
FROM wp_posts posts
LEFT JOIN wp_postmeta meta
	ON meta.post_id = posts.ID
WHERE
	posts.post_type = ''
ORDER BY
	meta.meta_key

/* Get a list of all used meta_values by meta_key, optionally by post_type. */
SELECT
	COUNT(meta.post_id),
	meta.meta_value
FROM wp_postmeta meta
INNER JOIN wp_posts posts
	ON posts.ID = meta.post_id AND posts.post_type = 'post'
WHERE
	meta.meta_key LIKE '%'
GROUP BY
	meta.meta_value
ORDER BY
	COUNT(meta.post_id) DESC,
	meta.meta_value

/* See how meta_values differ for similar meta_keys. */
SELECT
	m1.meta_value = m2.meta_value,
	m1.meta_value = m3.meta_value,
	m1.meta_value,
	m2.meta_value,
	m3.meta_value
FROM wp_postmeta m1
LEFT JOIN wp_postmeta m2
	ON m1.post_id = m2.post_id AND m2.meta_key = ''
LEFT JOIN wp_postmeta m3
	ON m3.post_id = m1.post_id AND m3.meta_key = ''
WHERE
	m1.meta_key = '' AND
	(m2.meta_id != NULL OR m3.meta_id != NULL)
