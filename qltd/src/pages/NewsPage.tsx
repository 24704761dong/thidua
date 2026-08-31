import logoImg from '@/assets/logo.png';
import React, { useEffect, useState } from 'react';
import { Page, Box, Text, Spinner } from 'zmp-ui';
import { useNews } from '@/hooks/useNews';
import { NewsCard } from '@/components/NewsCard';
import Header from '@/components/Header';

const NewsPage: React.FC = () => {
  // fetch up to 20 news for the list
  const { news, loading, error } = useNews(20);

  if (loading) {
    return (
      <Page className="flex items-center justify-center h-screen bg-transparent">
        <Spinner visible logo={logoImg} />
      </Page>
    );
  }

  return (
    <Page className="bg-transparent flex flex-col h-screen" hideScrollbar>
      <Header variant="back" title="Tin tức - Sự kiện" />
      
      <div className="flex-1 overflow-y-auto pb-24">
        <Box p={4} className="flex flex-col gap-2">
          {error ? (
            <div className="text-center py-10 text-slate-500">{error}</div>
          ) : news.length === 0 ? (
            <div className="text-center py-10 text-slate-500">Chưa có tin tức nào</div>
          ) : (
            <>
              {/* Featured article (first item) */}
              <NewsCard news={news[0]} variant="featured" />
              
              {/* List articles (remaining items) */}
              <div className="flex flex-col mt-2">
                {news.slice(1).map((item) => (
                  <NewsCard key={item.id} news={item} variant="list" />
                ))}
              </div>
            </>
          )}
        </Box>
      </div>
    </Page>
  );
};

export default NewsPage;
