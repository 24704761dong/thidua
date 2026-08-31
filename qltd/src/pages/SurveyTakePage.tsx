import logoImg from '@/assets/logo.png';
import React, { useState, useEffect } from 'react';
import { Page, Text, useSnackbar, Spinner } from 'zmp-ui';
import Header from '@/components/Header';
import { Icon } from '@/components/Icon';
import { useParams, useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { navigateBack } from '@/utils/navigation';

interface Question {
  id: string;
  title: string;
  description?: string;
  type: string;
  required: boolean;
  options: any;
  order: number;
}

interface SurveyDetail {
  id: string;
  title: string;
  description: string;
  badge: string;
  badgeType: string;
  dueDate: string;
  completed: boolean;
  banner_url?: string;
  submittedAt?: string;
}

const SurveyTakePage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { openSnackbar } = useSnackbar();
  const [survey, setSurvey] = useState<SurveyDetail | null>(null);
  const [questions, setQuestions] = useState<Question[]>([]);
  const [sections, setSections] = useState<Question[][]>([]);
  const [currentSectionIndex, setCurrentSectionIndex] = useState(0);
  const [answers, setAnswers] = useState<{ [key: string]: any }>({});
  const [otherInputs, setOtherInputs] = useState<{ [key: string]: string }>({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [uploadingFiles, setUploadingFiles] = useState<{ [key: string]: boolean }>({});
  const [activeDropdownQuestion, setActiveDropdownQuestion] = useState<Question | null>(null);

  useEffect(() => {
    const fetchSurveyDetail = async () => {
      try {
        setLoading(true);
        const res = await api.get(`/api/zalo/get-survey-detail?id=${id}`);
        if (res.data?.success && res.data?.survey) {
          setSurvey(res.data.survey);
          setQuestions(res.data.questions || []);

          // Group questions into sections based on 'section_header'
          const secs: Question[][] = [];
          let currentSec: Question[] = [];

          (res.data.questions || []).forEach((q: Question) => {
            if (q.type === 'section_header') {
              if (currentSec.length > 0) {
                secs.push(currentSec);
              }
              currentSec = [q];
            } else {
              currentSec.push(q);
            }
          });
          if (currentSec.length > 0) {
            secs.push(currentSec);
          }
          setSections(secs.length > 0 ? secs : [[]]);

          // Restore answers if completed
          if (res.data.survey.completed && res.data.answers) {
            setAnswers(res.data.answers);
          }
        } else {
          openSnackbar({ text: res.data?.message || 'Không thể tải chi tiết bài khảo sát', type: 'error' });
        }
      } catch (err: any) {
        openSnackbar({ text: err.response?.data?.message || 'Lỗi kết nối máy chủ', type: 'error' });
      } finally {
        setLoading(false);
      }
    };

    if (id) fetchSurveyDetail();
  }, [id]);

  const handleAnswerChange = (questionId: string, value: any) => {
    if (survey?.completed) return;
    setAnswers(prev => ({ ...prev, [questionId]: value }));
  };

  const handleCheckboxChange = (questionId: string, optionValue: string, isChecked: boolean) => {
    if (survey?.completed) return;
    const currentValues = Array.isArray(answers[questionId]) ? [...answers[questionId]] : [];
    if (isChecked) {
      if (!currentValues.includes(optionValue)) currentValues.push(optionValue);
    } else {
      const idx = currentValues.indexOf(optionValue);
      if (idx > -1) currentValues.splice(idx, 1);
    }
    setAnswers(prev => ({ ...prev, [questionId]: currentValues }));
  };

  const handleFileUpload = async (questionId: string, file: File) => {
    if (survey?.completed) return;
    try {
      setUploadingFiles(prev => ({ ...prev, [questionId]: true }));
      const formData = new FormData();
      formData.append('file', file);

      const res = await api.post('/api/zalo/upload-survey-file', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      if (res.data?.success && res.data?.file_url) {
        const curFiles = Array.isArray(answers[questionId]) ? [...answers[questionId]] : (answers[questionId] ? [answers[questionId]] : []);
        handleAnswerChange(questionId, [...curFiles, res.data.file_url]);
        openSnackbar({ text: 'Tải file lên Cloudflare R2 thành công!', type: 'success' });
      } else {
        openSnackbar({ text: res.data?.message || 'Tải file thất bại', type: 'error' });
      }
    } catch (err) {
      openSnackbar({ text: 'Lỗi upload file', type: 'error' });
    } finally {
      setUploadingFiles(prev => ({ ...prev, [questionId]: false }));
    }
  };

  const handleNextSection = () => {
    // Validate required questions in current section
    const currentSecQuestions = sections[currentSectionIndex] || [];
    for (const q of currentSecQuestions) {
      if (q.required && !survey?.completed) {
        const val = answers[q.id];
        if (val === undefined || val === '' || (Array.isArray(val) && val.length === 0)) {
          openSnackbar({ text: `Vui lòng trả lời câu hỏi bắt buộc: "${q.title}"`, type: 'warning' });
          return;
        }
      }
    }
    if (currentSectionIndex < sections.length - 1) {
      setCurrentSectionIndex(prev => prev + 1);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  const handlePrevSection = () => {
    if (currentSectionIndex > 0) {
      setCurrentSectionIndex(prev => prev - 1);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  const handleSubmit = async () => {
    // Validate all required questions in current section
    const currentSecQuestions = sections[currentSectionIndex] || [];
    for (const q of currentSecQuestions) {
      if (q.required && !survey?.completed) {
        const val = answers[q.id];
        if (val === undefined || val === '' || (Array.isArray(val) && val.length === 0)) {
          openSnackbar({ text: `Vui lòng trả lời câu hỏi bắt buộc: "${q.title}"`, type: 'warning' });
          return;
        }
      }
    }

    try {
      setSubmitting(true);
      const res = await api.post('/api/zalo/submit-survey', {
        survey_id: Number(id),
        answers: answers
      });

      if (res.data?.success) {
        openSnackbar({ text: res.data?.message || 'Nộp bài khảo sát thành công!', type: 'success' });
        setTimeout(() => {
          navigateBack(navigate);
        }, 1000);
      } else {
        openSnackbar({ text: res.data?.message || 'Nộp bài thất bại', type: 'error' });
      }
    } catch (err: any) {
      openSnackbar({ text: err.response?.data?.message || 'Lỗi kết nối máy chủ', type: 'error' });
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <Page className="bg-[#F3F4F6] min-h-screen flex flex-col items-center justify-center">
        <Header title="Khảo sát ý kiến" showBackIcon={true} />
        <div className="flex flex-col items-center justify-center p-8 space-y-4">
          <Spinner visible={true} logo={logoImg} />
          <Text className="text-sm font-semibold text-slate-500 animate-pulse">Đang tải phiếu khảo sát...</Text>
        </div>
      </Page>
    );
  }

  if (!survey) {
    return (
      <Page className="bg-[#F3F4F6] min-h-screen flex flex-col">
        <Header title="Khảo sát ý kiến" showBackIcon={true} />
        <div className="flex-1 flex flex-col items-center justify-center p-6 text-center">
          <Icon name="Notification" size={48} className="text-slate-300 mb-2" />
          <Text className="text-base font-bold text-slate-700">Không tìm thấy bài khảo sát</Text>
          <Text className="text-xs text-slate-500 mt-1">Bài khảo sát đã bị xóa hoặc hết hạn.</Text>
        </div>
      </Page>
    );
  }

  const currentSecQuestions = sections[currentSectionIndex] || [];
  const firstQuestionIsHeader = currentSecQuestions[0]?.type === 'section_header';
  const sectionHeader = firstQuestionIsHeader ? currentSecQuestions[0] : null;
  const questionsToRender = firstQuestionIsHeader ? currentSecQuestions.slice(1) : currentSecQuestions;

  return (
    <Page className="bg-[#F3F4F6] min-h-screen flex flex-col pb-24 relative">
      <Header title="Khảo sát ý kiến" showBackIcon={true} />

      {/* Main Banner / Survey Title (Google Forms Style) */}
      <div className="p-4 md:p-6 space-y-4 max-w-3xl mx-auto w-full">
        <div className="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
          {survey.banner_url ? (
            <div className="w-full aspect-[21/9] bg-slate-100 overflow-hidden">
              <img src={survey.banner_url} alt="Survey Banner" className="w-full h-full object-cover" />
            </div>
          ) : (
            <div className="h-2 bg-[#224397] w-full"></div>
          )}

          <div className="p-5 space-y-3">
            <div className="flex items-center justify-between">
              <span className={`text-xs font-bold ${
                survey.badgeType === 'expired' ? 'text-rose-500' :
                survey.badgeType === 'required' ? 'text-rose-600' :
                survey.badgeType === 'completed' ? 'text-emerald-600' :
                'text-blue-600'
              }`}>
                {survey.badge}
              </span>
              <Text className="text-xs font-medium text-slate-500">Hạn: {survey.dueDate}</Text>
            </div>

            <Text
              className="text-xl leading-snug"
              style={{
                color: survey.style?.color || '#1e293b',
                fontWeight: survey.style?.bold ? 'bold' : 'normal',
                fontStyle: survey.style?.italic ? 'italic' : 'normal',
                textDecoration: survey.style?.underline ? 'underline' : 'none'
              }}
            >
              {survey.title}
            </Text>
            <Text className="text-xs font-medium text-slate-600 leading-relaxed whitespace-pre-line">{survey.description}</Text>

            {survey.completed && (
              <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 flex items-center gap-3 mt-4 shadow-sm">
                <div className="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
                  ✓
                </div>
                <div>
                  <Text className="text-xs font-bold text-emerald-800">Bạn đã nộp bài khảo sát này</Text>
                  <Text className="text-[11px] text-emerald-600 mt-0.5">Thời gian ghi nhận: {survey.submittedAt}</Text>
                </div>
              </div>
            )}

            {/* Progress / Section Bar */}
            {sections.length > 1 && (
              <div className="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#224397]">
                <span>Phần {currentSectionIndex + 1} / {sections.length}</span>
                <div className="w-1/2 bg-slate-100 h-2 rounded-full overflow-hidden">
                  <div className="bg-[#224397] h-full transition-all duration-300" style={{ width: `${((currentSectionIndex + 1) / sections.length) * 100}%` }}></div>
                </div>
              </div>
            )}
          </div>
        </div>

        {/* SECTION HEADER CARD */}
        {sectionHeader && (
          <div className="bg-[#224397] text-white rounded-2xl p-5 shadow-sm space-y-1.5">
            <Text className="text-base font-extrabold leading-snug">{sectionHeader.title}</Text>
            {sectionHeader.description && (
              <Text className="text-xs text-white/80 leading-relaxed">{sectionHeader.description}</Text>
            )}
          </div>
        )}

        {/* QUESTIONS */}
        <div className="space-y-4">
          {questionsToRender.map((q, idx) => {
            const val = answers[q.id];

            return (
              <div key={q.id} className="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4 hover:shadow-md transition">
                <div className="space-y-1">
                  <div className="flex items-start gap-2">
                    <Text
                      className="text-sm leading-snug flex-1"
                      style={{
                        color: q.options?.style?.color || '#1e293b',
                        fontWeight: q.options?.style?.bold ? 'bold' : 'normal',
                        fontStyle: q.options?.style?.italic ? 'italic' : 'normal',
                        textDecoration: q.options?.style?.underline ? 'underline' : 'none'
                      }}
                    >
                      {q.title} {q.required && <span className="text-rose-500 font-normal not-italic no-underline ml-1">*</span>}
                    </Text>
                  </div>
                  {q.description && (
                    <Text className="text-xs text-slate-500 leading-relaxed">{q.description}</Text>
                  )}
                </div>

                {/* RENDER DYNAMIC INPUTS */}
                <div className="pt-2">
                  {/* SHORT TEXT */}
                  {q.type === 'short_text' && (
                    <input
                      type="text"
                      disabled={survey.completed}
                      value={val || ''}
                      onChange={e => handleAnswerChange(q.id, e.target.value)}
                      placeholder="Câu trả lời của bạn..."
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                    />
                  )}

                  {/* LONG TEXT */}
                  {q.type === 'long_text' && (
                    <textarea
                      rows={3}
                      disabled={survey.completed}
                      value={val || ''}
                      onChange={e => handleAnswerChange(q.id, e.target.value)}
                      placeholder="Câu trả lời chi tiết của bạn..."
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                    ></textarea>
                  )}

                  {/* RADIO (1 ĐÁP ÁN) */}
                  {q.type === 'radio' && (
                    <div className="space-y-3">
                      {(q.options?.options || []).map((opt: string) => (
                        <label key={opt} className="flex items-center gap-3 cursor-pointer text-xs font-semibold text-slate-700 hover:text-slate-900 transition">
                          <input
                            type="radio"
                            name={`radio_${q.id}`}
                            disabled={survey.completed}
                            checked={val === opt}
                            onChange={() => handleAnswerChange(q.id, opt)}
                            className="w-4 h-4 accent-[#224397]"
                          />
                          <span>{opt}</span>
                        </label>
                      ))}
                      {q.options?.has_other && (
                        <div className="flex items-center gap-3 pt-1">
                          <label className="flex items-center gap-3 cursor-pointer text-xs font-semibold text-slate-700">
                            <input
                              type="radio"
                              name={`radio_${q.id}`}
                              disabled={survey.completed}
                              checked={val !== undefined && !(q.options?.options || []).includes(val)}
                              onChange={() => handleAnswerChange(q.id, otherInputs[q.id] || 'Mục khác')}
                              className="w-4 h-4 accent-[#224397]"
                            />
                            <span>Khác:</span>
                          </label>
                          <input
                            type="text"
                            disabled={survey.completed}
                            value={val !== undefined && !(q.options?.options || []).includes(val) ? val : otherInputs[q.id] || ''}
                            onChange={e => {
                              setOtherInputs(prev => ({ ...prev, [q.id]: e.target.value }));
                              handleAnswerChange(q.id, e.target.value);
                            }}
                            placeholder="Vui lòng ghi rõ..."
                            className="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-medium text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                          />
                        </div>
                      )}
                    </div>
                  )}

                  {/* CHECKBOX (NHIỀU ĐÁP ÁN) */}
                  {q.type === 'checkbox' && (
                    <div className="space-y-3">
                      {(q.options?.options || []).map((opt: string) => {
                        const isChecked = Array.isArray(val) && val.includes(opt);
                        return (
                          <label key={opt} className="flex items-center gap-3 cursor-pointer text-xs font-semibold text-slate-700 hover:text-slate-900 transition">
                            <input
                              type="checkbox"
                              disabled={survey.completed}
                              checked={isChecked}
                              onChange={e => handleCheckboxChange(q.id, opt, e.target.checked)}
                              className="w-4 h-4 accent-[#224397] rounded"
                            />
                            <span>{opt}</span>
                          </label>
                        );
                      })}
                      {q.options?.has_other && (
                        <div className="flex items-center gap-3 pt-1">
                          <label className="flex items-center gap-3 cursor-pointer text-xs font-semibold text-slate-700">
                            <input
                              type="checkbox"
                              disabled={survey.completed}
                              checked={Array.isArray(val) && val.some(v => !(q.options?.options || []).includes(v))}
                              onChange={e => {
                                const customVal = otherInputs[q.id] || 'Mục khác';
                                handleCheckboxChange(q.id, customVal, e.target.checked);
                              }}
                              className="w-4 h-4 accent-[#224397] rounded"
                            />
                            <span>Khác:</span>
                          </label>
                          <input
                            type="text"
                            disabled={survey.completed}
                            value={Array.isArray(val) ? val.find(v => !(q.options?.options || []).includes(v)) || otherInputs[q.id] || '' : otherInputs[q.id] || ''}
                            onChange={e => {
                              const oldCustom = otherInputs[q.id] || 'Mục khác';
                              const newVal = e.target.value;
                              setOtherInputs(prev => ({ ...prev, [q.id]: newVal }));
                              if (Array.isArray(val) && val.includes(oldCustom)) {
                                const filtered = val.filter(v => v !== oldCustom);
                                handleAnswerChange(q.id, [...filtered, newVal]);
                              }
                            }}
                            placeholder="Vui lòng ghi rõ..."
                            className="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-medium text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                          />
                        </div>
                      )}
                    </div>
                  )}

                  {/* DROPDOWN */}
                  {q.type === 'dropdown' && (
                    <div>
                      <button
                        type="button"
                        disabled={survey.completed}
                        onClick={() => setActiveDropdownQuestion(q)}
                        className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-xs font-semibold text-slate-800 flex items-center justify-between hover:bg-slate-100 transition focus:border-[#224397] outline-none disabled:bg-slate-100"
                      >
                        <span>{val || '-- Chọn đáp án --'}</span>
                        <Icon name="ChevronDown" size={16} className="text-slate-400" />
                      </button>
                    </div>
                  )}

                  {/* FILE UPLOAD (R2) */}
                  {q.type === 'file_upload' && (
                    <div className="space-y-3">
                      {Array.isArray(val) && val.length > 0 && (
                        <div className="space-y-2">
                          {val.map((fileUrl: string, fIdx: number) => (
                            <div key={fIdx} className="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center justify-between shadow-sm">
                              <div className="flex items-center gap-2 overflow-hidden">
                                <Icon name="Attach" size={20} className="text-[#224397] shrink-0" />
                                <a href={fileUrl} target="_blank" rel="noreferrer" className="text-xs font-bold text-[#224397] underline truncate">
                                  {fileUrl.split('/').pop()}
                                </a>
                              </div>
                              {!survey.completed && (
                                <button onClick={() => {
                                  const newFiles = val.filter((_, i) => i !== fIdx);
                                  handleAnswerChange(q.id, newFiles);
                                }} className="text-rose-500 hover:text-rose-700 font-bold px-2 py-1 text-xs">
                                  Xóa
                                </button>
                              )}
                            </div>
                          ))}
                        </div>
                      )}
                      {!survey.completed && (
                        <div className="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-[#224397] transition bg-slate-50 relative">
                          <input
                            type="file"
                            disabled={survey.completed || uploadingFiles[q.id]}
                            onChange={e => e.target.files?.[0] && handleFileUpload(q.id, e.target.files[0])}
                            className="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                          />
                          <div className="flex flex-col items-center justify-center space-y-1.5">
                            {uploadingFiles[q.id] ? (
                              <>
                                <Spinner visible={true} />
                                <Text className="text-xs font-bold text-[#224397]">Đang tải lên Cloudflare R2...</Text>
                              </>
                            ) : (
                              <>
                                <Icon name="Attach" size={32} className="text-[#224397]" />
                                <Text className="text-xs font-bold text-slate-700">Bấm hoặc kéo thả file vào đây</Text>
                                <Text className="text-[11px] text-slate-500">Hỗ trợ JPG, PNG, PDF, DOCX (Lưu trực tiếp trên R2)</Text>
                              </>
                            )}
                          </div>
                        </div>
                      )}
                    </div>
                  )}

                  {/* LINEAR SCALE (1-10) */}
                  {q.type === 'linear_scale' && (
                    <div className="space-y-4 pt-2">
                      <div className="flex items-center justify-between text-xs font-bold text-slate-600 px-1">
                        <span>{q.options?.label_min || '1'}</span>
                        <span>{q.options?.label_max || '10'}</span>
                      </div>
                      <div className="flex items-center justify-between gap-1 overflow-x-auto py-2">
                        {Array.from({ length: (q.options?.scale_max || 10) - (q.options?.scale_min || 1) + 1 }, (_, i) => i + (q.options?.scale_min || 1)).map(num => (
                          <button
                            key={num}
                            type="button"
                            disabled={survey.completed}
                            onClick={() => handleAnswerChange(q.id, num)}
                            className={`w-10 h-10 rounded-2xl font-black text-xs flex items-center justify-center shrink-0 shadow-sm transition ${Number(val) === num
                              ? 'bg-[#224397] text-white shadow-md scale-105'
                              : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200'
                              }`}
                          >
                            {num}
                          </button>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* STAR RATING */}
                  {q.type === 'star_rating' && (
                    <div className="flex items-center gap-2 pt-1">
                      {[1, 2, 3, 4, 5].map(star => (
                        <button
                          key={star}
                          type="button"
                          disabled={survey.completed}
                          onClick={() => handleAnswerChange(q.id, star)}
                          className="p-1 focus:outline-none transition hover:scale-110"
                        >
                          <span className={`text-3xl ${Number(val) >= star ? 'text-[#FAB723]' : 'text-slate-200'}`}>
                            ★
                          </span>
                        </button>
                      ))}
                      {val && <Text className="text-xs font-bold text-slate-600 ml-2">({val} sao)</Text>}
                    </div>
                  )}

                  {/* GRID RADIO */}
                  {q.type === 'grid_radio' && (
                    <div className="overflow-x-auto list-scrollbar pt-2">
                      <table className="w-full text-left text-xs text-slate-700 min-w-[400px]">
                        <thead>
                          <tr className="border-b border-slate-200 bg-slate-50">
                            <th className="p-3 w-1/2"></th>
                            {(q.options?.grid_cols || []).map((col: string) => (
                              <th key={col} className="p-3 text-center font-bold text-[#224397]">{col}</th>
                            ))}
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                          {(q.options?.grid_rows || []).map((row: string) => (
                            <tr key={row} className="hover:bg-slate-50 transition">
                              <td className="p-3 font-semibold text-slate-800">{row}</td>
                              {(q.options?.grid_cols || []).map((col: string) => (
                                <td key={col} className="p-3 text-center">
                                  <input
                                    type="radio"
                                    name={`grid_${q.id}_${row}`}
                                    disabled={survey.completed}
                                    checked={val?.[row] === col}
                                    onChange={() => handleAnswerChange(q.id, { ...(val || {}), [row]: col })}
                                    className="w-4 h-4 accent-[#224397]"
                                  />
                                </td>
                              ))}
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}

                  {/* GRID CHECKBOX */}
                  {q.type === 'grid_checkbox' && (
                    <div className="overflow-x-auto list-scrollbar pt-2">
                      <table className="w-full text-left text-xs text-slate-700 min-w-[400px]">
                        <thead>
                          <tr className="border-b border-slate-200 bg-slate-50">
                            <th className="p-3 w-1/2"></th>
                            {(q.options?.grid_cols || []).map((col: string) => (
                              <th key={col} className="p-3 text-center font-bold text-[#224397]">{col}</th>
                            ))}
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                          {(q.options?.grid_rows || []).map((row: string) => (
                            <tr key={row} className="hover:bg-slate-50 transition">
                              <td className="p-3 font-semibold text-slate-800">{row}</td>
                              {(q.options?.grid_cols || []).map((col: string) => {
                                const isChecked = Array.isArray(val?.[row]) && val[row].includes(col);
                                return (
                                  <td key={col} className="p-3 text-center">
                                    <input
                                      type="checkbox"
                                      disabled={survey.completed}
                                      checked={isChecked}
                                      onChange={e => {
                                        const curRowVals = Array.isArray(val?.[row]) ? [...val[row]] : [];
                                        if (e.target.checked) {
                                          if (!curRowVals.includes(col)) curRowVals.push(col);
                                        } else {
                                          const idx = curRowVals.indexOf(col);
                                          if (idx > -1) curRowVals.splice(idx, 1);
                                        }
                                        handleAnswerChange(q.id, { ...(val || {}), [row]: curRowVals });
                                      }}
                                      className="w-4 h-4 accent-[#224397] rounded"
                                    />
                                  </td>
                                );
                              })}
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}

                  {/* DATE */}
                  {q.type === 'date' && (
                    <input
                      type="date"
                      disabled={survey.completed}
                      value={val || ''}
                      onChange={e => handleAnswerChange(q.id, e.target.value)}
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                    />
                  )}

                  {/* TIME */}
                  {q.type === 'time' && (
                    <input
                      type="time"
                      disabled={survey.completed}
                      value={val || ''}
                      onChange={e => handleAnswerChange(q.id, e.target.value)}
                      className="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:border-[#224397] outline-none transition disabled:bg-slate-100"
                    />
                  )}
                </div>
              </div>
            );
          })}
        </div>

        {/* NAVIGATION / SUBMIT BUTTONS */}
        <div className="flex items-center justify-between mt-6 mb-4">
          {currentSectionIndex > 0 ? (
            <button
              onClick={handlePrevSection}
              className="px-5 py-2.5 bg-slate-100 text-slate-700 font-extrabold rounded-xl text-xs hover:bg-slate-200 transition shadow-sm border border-slate-200"
            >
              Quay lại
            </button>
          ) : (
            <div></div>
          )}

          {currentSectionIndex < sections.length - 1 ? (
            <button
              onClick={handleNextSection}
              className="px-6 py-2.5 bg-[#224397] text-white font-extrabold rounded-xl text-xs hover:bg-[#FAB723] hover:text-slate-900 transition shadow-md"
            >
              Tiếp theo
            </button>
          ) : !survey.completed ? (
            <button
              disabled={submitting}
              onClick={handleSubmit}
              className="px-8 py-3 bg-[#224397] text-white font-extrabold rounded-xl text-xs hover:bg-[#FAB723] hover:text-slate-900 transition shadow-md flex items-center gap-2 disabled:opacity-50"
            >
              <span>Gửi Khảo Sát</span>
              <Icon icon="zi-chevron-right" size={16} />
            </button>
          ) : (
            <button
              onClick={() => navigateBack(navigate)}
              className="px-6 py-2.5 bg-[#224397] text-white font-extrabold rounded-xl text-xs hover:bg-[#FAB723] hover:text-slate-900 transition shadow-md"
            >
              Quay lại danh sách
            </button>
          )}
        </div>
      </div>

      {/* BOTTOM SHEET MENU CHỌN ĐÁP ÁN */}
      {activeDropdownQuestion && (
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 backdrop-blur-sm transition-opacity animate-fade-in" onClick={() => setActiveDropdownQuestion(null)}>
          <div
            className="bg-white w-full max-w-3xl rounded-t-3xl shadow-2xl overflow-hidden animate-slide-up flex flex-col max-h-[80vh]"
            onClick={e => e.stopPropagation()}
          >
            <div className="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
              <Text className="text-sm font-extrabold text-[#224397] truncate pr-4">{activeDropdownQuestion.title}</Text>
              <button
                onClick={() => setActiveDropdownQuestion(null)}
                className="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-300 transition font-bold text-xs shrink-0"
              >
                ✕
              </button>
            </div>
            <div className="overflow-y-auto p-4 pb-28 space-y-2 list-scrollbar max-h-[60vh]">
              {(activeDropdownQuestion.options?.options || []).map((opt: string) => {
                const isSelected = answers[activeDropdownQuestion.id] === opt;
                return (
                  <button
                    key={opt}
                    type="button"
                    onClick={() => {
                      handleAnswerChange(activeDropdownQuestion.id, opt);
                      setActiveDropdownQuestion(null);
                    }}
                    className={`w-full text-left p-3.5 rounded-2xl flex items-center justify-between text-xs font-bold transition ${isSelected
                      ? 'bg-[#224397] text-white shadow-md shadow-[#224397]/20 scale-[1.01]'
                      : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/60'
                      }`}
                  >
                    <span>{opt}</span>
                    {isSelected && <span className="text-white font-black text-sm">✓</span>}
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}

      {/* OVERLAY LOADING KHI NỘP BÀI */}
      {submitting && (
        <div className="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white/80 backdrop-blur-sm animate-fade-in">
          <Spinner visible={true} logo={logoImg} />
          <Text className="mt-4 text-sm font-bold text-[#224397]">Đang nộp bài...</Text>
        </div>
      )}
    </Page>
  );
};

export default SurveyTakePage;
