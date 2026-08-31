import React from 'react';
import { useNavigate } from 'react-router-dom';
import { NewsItem } from '@/hooks/useNews';
import { openWebview } from 'zmp-sdk/apis';

interface NewsCardProps {
  news: NewsItem;
  variant?: 'horizontal' | 'list' | 'featured';
}

const formatDate = (dateString: string) => {
  try {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    }).format(date);
  } catch {
    return dateString;
  }
};

export const NewsCard: React.FC<NewsCardProps> = ({ news, variant = 'list' }) => {
  const defaultImage = 'https://images.unsplash.com/photo-1496307042754-b4aa456c4a2d?auto=format&fit=crop&w=1200&q=70';
  const imgUrl = news.thumbnail_url || defaultImage;

  const handleClick = () => {
    // Open the news article in Zalo Mini App's built-in webview
    openWebview({
      url: `https://c3binhson.edu.vn/tin-tuc/${news.id}`,
      config: {
        style: "bottomSheet",
        leftButton: "back"
      }
    });
  };

  if (variant === 'featured') {
    return (
      <div className="flex flex-col mb-6 cursor-pointer active:opacity-80" onClick={handleClick}>
        <div className="w-full aspect-[16/10] rounded-xl overflow-hidden mb-3 bg-slate-100">
          <img src={imgUrl} alt={news.title} className="w-full h-full object-cover" />
        </div>
        <h3 className="font-bold text-[16px] text-slate-800 line-clamp-3 leading-snug">
          {news.title}
        </h3>
        <p className="text-slate-500 text-xs mt-2">{formatDate(news.published_at)}</p>
      </div>
    );
  }

  if (variant === 'horizontal') {
    return (
      <div className="flex flex-col w-full h-auto min-h-[170px] bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden cursor-pointer active:opacity-80" onClick={handleClick}>
        <div className="w-full h-[100px] bg-slate-100 shrink-0">
          <img src={imgUrl} alt={news.title} className="w-full h-full object-cover" />
        </div>
        <div className="p-3 flex flex-col justify-between flex-1">
          <h3 className="font-bold text-[13px] text-slate-800 line-clamp-2 leading-[18px] h-[36px] whitespace-normal break-words">
            {news.title}
          </h3>
          <p className="text-slate-400 text-[11px] mt-1">{formatDate(news.published_at)}</p>
        </div>
      </div>
    );
  }

  // list variant (image on left)
  return (
    <div className="flex items-start gap-3 py-3 border-b border-slate-100 last:border-b-0 cursor-pointer active:opacity-80" onClick={handleClick}>
      <div className="w-[120px] h-[80px] shrink-0 rounded-lg overflow-hidden bg-slate-100">
        <img src={imgUrl} alt={news.title} className="w-full h-full object-cover" />
      </div>
      <div className="flex flex-col flex-1 py-1">
        <h3 className="font-bold text-[14px] text-slate-800 line-clamp-2 leading-snug mb-2">
          {news.title}
        </h3>
        <p className="text-slate-500 text-xs">{formatDate(news.published_at)}</p>
      </div>
    </div>
  );
};
