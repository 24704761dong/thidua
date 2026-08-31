import React, { useState, useEffect } from 'react';
import { TransformWrapper, TransformComponent } from 'react-zoom-pan-pinch';
import { saveImageToGallery, getSystemInfo } from 'zmp-sdk/apis';
import Portal from './Portal';

interface CustomImageViewerProps {
  visible: boolean;
  src: string | null;
  onClose: () => void;
}

const CustomImageViewer: React.FC<CustomImageViewerProps> = ({ visible, src, onClose }) => {
  const [rotation, setRotation] = useState(0);
  const [downloading, setDownloading] = useState(false);
  const [localToast, setLocalToast] = useState<{ msg: string, type: 'success' | 'error' | 'info' } | null>(null);

  const [isRendered, setIsRendered] = useState(false);
  const [isVisible, setIsVisible] = useState(false);
  const [currentSrc, setCurrentSrc] = useState<string | null>(null);

  useEffect(() => {
    if (visible && src) {
      setCurrentSrc(src);
      setIsRendered(true);
      requestAnimationFrame(() => {
        requestAnimationFrame(() => setIsVisible(true));
      });
    } else {
      setIsVisible(false);
      setTimeout(() => setRotation(0), 300);
      setLocalToast(null);
      const timer = setTimeout(() => {
        setIsRendered(false);
        setCurrentSrc(null);
      }, 300); // Wait for transition
      return () => clearTimeout(timer);
    }
  }, [visible, src]);

  if (!isRendered || !currentSrc) return null;

  const showToast = (msg: string, type: 'success' | 'error' | 'info' = 'info') => {
    setLocalToast({ msg, type });
    setTimeout(() => setLocalToast(null), 3000);
  };

  const handleDownload = async () => {
    if (downloading) return;
    setDownloading(true);
    try {
      let platform = 'unknown';
      try {
        const sysInfo = getSystemInfo();
        platform = sysInfo.platform || 'unknown';
      } catch (e) {}

      // Môi trường PC / Trình duyệt hoặc Studio
      if (platform.toLowerCase() !== 'android' && platform.toLowerCase() !== 'ios' && platform.toLowerCase() !== 'unknown') {
        const link = document.createElement('a');
        link.href = src;
        link.target = '_blank';
        link.download = `minh-chung-${Date.now()}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('Đang mở ảnh để lưu trên máy tính...', 'info');
      } else {
        // App Zalo thực tế trên mobile
        await saveImageToGallery({
          imageUrl: src,
        });
        showToast('Đã lưu ảnh vào thư viện thiết bị', 'success');
      }
    } catch (error) {
      console.error(error);
      
      // Dự phòng nếu saveImageToGallery bị lỗi do máy tính giả lập
      try {
        const link = document.createElement('a');
        link.href = src;
        link.target = '_blank';
        link.download = `minh-chung-${Date.now()}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('Lưu bằng API lỗi, đang mở ảnh trên trình duyệt...', 'info');
      } catch(e2) {
        showToast('Lưu ảnh thất bại, vui lòng thử lại', 'error');
      }
    } finally {
      setDownloading(false);
    }
  };

  return (
    <Portal>
      <div className={`fixed inset-0 z-[100000] bg-black/95 flex items-center justify-center overflow-hidden touch-none transition-opacity duration-300 ease-out ${isVisible ? 'opacity-100' : 'opacity-0'}`}>
        
        {/* Toast custom */}
        {localToast && (
          <div className={`absolute bottom-20 left-1/2 transform -translate-x-1/2 px-5 py-2.5 rounded-full text-sm font-medium z-[100001] shadow-2xl pointer-events-none whitespace-nowrap transition-all duration-300 ${
            localToast.type === 'success' ? 'bg-green-600/90 text-white' : 
            localToast.type === 'error' ? 'bg-red-600/90 text-white' : 
            'bg-gray-800/90 text-white'
          }`}>
            {localToast.msg}
          </div>
        )}

        {/* Header */}
        <div className="absolute top-0 left-0 right-0 p-4 pt-8 flex justify-between items-center z-50 pointer-events-none">
          <div className="flex gap-3 pointer-events-auto">
            <div 
              className="w-11 h-11 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white cursor-pointer active:bg-white/40 transition-all shadow-lg"
              onClick={handleDownload}
            >
              {downloading ? (
                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              ) : (
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              )}
            </div>
            <div 
              className="w-11 h-11 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white cursor-pointer active:bg-white/40 transition-all shadow-lg"
              onClick={() => setRotation(r => r - 90)}
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            </div>
            <div 
              className="w-11 h-11 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white cursor-pointer active:bg-white/40 transition-all shadow-lg"
              onClick={() => setRotation(r => r + 90)}
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
            </div>
          </div>
          <div 
            className="w-11 h-11 bg-red-500/90 backdrop-blur-md rounded-full flex items-center justify-center text-white cursor-pointer active:bg-red-600 transition-all shadow-lg pointer-events-auto"
            onClick={onClose}
          >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
          </div>
        </div>

        {/* Viewer */}
        <div className={`w-full h-full flex items-center justify-center transition-transform duration-300 ease-out ${isVisible ? 'scale-100' : 'scale-90'}`}>
          <TransformWrapper
            initialScale={1}
            minScale={0.5}
            maxScale={5}
            centerOnInit
            wheel={{ step: 0.03, smoothStep: 0.01 }}
            pinch={{ step: 2 }}
            doubleClick={{ step: 0.5 }}
          >
            <TransformComponent wrapperStyle={{ width: '100%', height: '100%' }}>
              <img 
                src={currentSrc} 
                alt="Minh chứng" 
                style={{ 
                  transform: `rotate(${rotation}deg)`, 
                  transition: 'transform 0.3s ease',
                  width: '100vw',
                  height: '100vh',
                  objectFit: 'contain'
                }} 
              />
            </TransformComponent>
          </TransformWrapper>
        </div>
      </div>
    </Portal>
  );
};

export default CustomImageViewer;
