import { useState, useEffect } from 'react';

export interface NewsItem {
  id: number;
  type: string;
  title: string;
  excerpt: string;
  thumbnail_url: string;
  published_at: string;
}

export const useNews = (limit: number = 6) => {
  const [news, setNews] = useState<NewsItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchNews = async () => {
      try {
        setLoading(true);
        setError(null);
        
        // Call news API
        const response = await fetch(`https://c3binhson.edu.vn/api/news.php?type=news&limit=${limit}`);
        const data = await response.json();
        
        if (data.ok && data.data) {
          const parsedData = data.data.map((item: any) => {
            let thumb = item.thumbnail_url;
            if (thumb && thumb.startsWith('/')) {
              thumb = 'https://c3binhson.edu.vn' + thumb;
            } else if (thumb && thumb.startsWith('http://')) {
              thumb = 'https://' + thumb.substring(7);
            }
            if (thumb) {
              thumb = `https://c3binhson.edu.vn/thidua/api/zalo/image-proxy?url=${encodeURIComponent(thumb)}`;
            }
            return { ...item, thumbnail_url: thumb };
          });
          setNews(parsedData);
        } else {
          throw new Error('Invalid data format');
        }
      } catch (err) {
        console.error('Failed to fetch news:', err);
        setError('Không thể tải tin tức');
      } finally {
        setLoading(false);
      }
    };

    fetchNews();
  }, [limit]);

  return { news, loading, error };
};
